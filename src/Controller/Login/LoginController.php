<?php
require_once '../../../config/cors.php';
require_once '../../../config/database.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

/**
 * Authentifie un utilisateur et renvoie un jeton ou une session.
 */
function authenticateUser($mail, $password) {
    $conn = connectDb();
    $sql = "SELECT * FROM users WHERE mail = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $mail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Générer un jeton ou créer une session
            $response = [
                'status' => 'success',
                'message' => 'Authentication successful',
                'user' => [
                    'mail' => $user['mail'],
                    'firstname' => $user['firstname'],
                    'lastname' => $user['lastname'],
                ]
            ];
            return $response;
        } else {
            // Mot de passe incorrect
            return [
                'status' => 'error',
                'message' => 'Invalid email or password.'
            ];
        }
    } else {
        // Utilisateur non trouvé
        return [
            'status' => 'error',
            'message' => 'Invalid email or password.'
        ];
    }
}

// Traiter la requête POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data['mail'] ?? '';
    $password = $data['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Email and password are required.']);
    } else {
        $result = authenticateUser($email, $password);
        echo json_encode($result);
    }
}
?>
