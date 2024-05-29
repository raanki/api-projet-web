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
        $sql .= " WHERE student_number IS NULL";
    } elseif ($role === 'student') {
        $sql .= " WHERE student_number IS NOT NULL AND WHERE password is NULL";
    } else {
        // Si le rôle n'est ni 'admin' ni 'student', retourner une erreur
        return ['error' => 'Invalid role provided'];
    }

    $result = $conn->query($sql);
    $users = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        $result->free();
    }

    $conn->close();
    return $users;
}

// Traiter la requête GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Récupérer le rôle à partir des paramètres de requête
    $role = $_GET['role'] ?? '';

    // Récupérer les utilisateurs en fonction du rôle
    $result = getUsersByRole($role);
    echo json_encode($result);
}
