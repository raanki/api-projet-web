<?php
require_once '../../../config/cors.php';
require_once '../../../config/database.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

/**
 * Fonction pour récupérer les utilisateurs en fonction de leur rôle (admin ou student)
 */
function getUsersByRole($role) {
    $conn = connectDb();
    $sql = "SELECT * FROM users";

    // Appliquer un filtre en fonction du rôle
    if ($role === 'admin') {
        $sql .= " WHERE password IS NOT NULL AND password != ''";
    } elseif ($role === 'student') {
        $sql .= " WHERE student_number IS NOT NULL AND (password is NULL OR password = '') ";
    } else {
        // Si le rôle n'est ni 'admin' ni 'student', retourner une erreur
        return ['error' => 'Invalid role provided'];
    }

    $result = $conn->query($sql);
    $users = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $row["firstname"] = ucfirst($row["firstname"]);
            $row["lastname"] = ucfirst($row["lastname"]);
            $row["job"] = ucfirst($row["job"]);
            $users[] = $row;
        }
        $result->free();
    }

    $conn->close();
    return $users;
}

/**
 * Fonction pour créer un nouvel utilisateur
 */
function createUser($data) {
    $conn = connectDb();
    $sql = "INSERT INTO users (mail, student_number, firstname, lastname, address, password, phone, job, birth_date, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $conn->prepare($sql);

    $password_hash = password_hash($data['password'], PASSWORD_DEFAULT, []);
    $stmt->bind_param("sisssssss", $data['mail'], $data['student_number'], $data['firstname'], $data['lastname'], $data['address'], $password_hash, $data['phone'], $data['job'], $data['birth_date']);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        return '';
    } else {
        $stmt->close();
        $conn->close();
        return ['error' => 'Unable to create user.'];
    }
}

/**
 * Fonction pour mettre à jour un utilisateur existant
 */
function updateUser($data) {
    $conn = connectDb();
    $sql = "UPDATE users SET student_number = ?, firstname = ?, lastname = ?, address = ?, password = ?, phone = ?, job = ?, birth_date = ? WHERE mail = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("issssssss", $data['student_number'], $data['firstname'], $data['lastname'], $data['address'], $data['password'], $data['phone'], $data['job'], $data['birth_date'], $data['mail']);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        return '';
    } else {
        $stmt->close();
        $conn->close();
        return ['error' => 'Unable to update user.'];
    }
}

/**
 * Fonction pour récupérer un utilisateur par son mail
 */
function getUserByMail($mail) {
    $conn = connectDb();
    $sql = "SELECT * FROM users WHERE mail = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $mail);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $user;
}

/**
 * Fonction pour supprimer un utilisateur par son mail
 */
function deleteUser($mail) {
    $conn = connectDb();
    $sql = "DELETE FROM users WHERE mail = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $mail);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}

// Traiter les requêtes
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    $action = $_GET['action'] ?? '';
    $mail = $_GET['mail'] ?? '';
    $filter = $_GET['filter'] ?? '';
    $order = $_GET['order'] ?? '';


    // Traiter la requête GET
    if (!empty($_GET['role'])) {
        $role = $_GET['role'] ?? '';

        $result = getUsersByRole($role);
        echo json_encode($result);
    }
    else if ($action == 'fetch' && !empty($mail)) {
        $user = getUserByMail($mail);
        echo json_encode($user);
    }
} elseif ($method == 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    $action = $data['action'] ?? '';

    if ($action == 'fetch') {
        echo json_encode(getUserByMail($data['mail']));
    } elseif ($action == 'create') {
        $newUser = createUser($data);
        echo json_encode($newUser);
    } elseif ($action == 'update') {
        $updatedUser = updateUser($data);
        echo json_encode($updatedUser);
    }
} elseif ($method == 'DELETE') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    $mail = $data['id'] ?? null;

    if ($mail) {
        $success = deleteUser($mail);
        if ($success) {
            echo json_encode(['success' => 'User deleted']);
        } else {
            echo json_encode(['error' => 'Unable to delete user']);
        }
    } else {
        echo json_encode(['error' => 'No mail provided']);
    }
}
?>

