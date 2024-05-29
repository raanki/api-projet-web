<?php
require_once '../../../config/cors.php';
require_once '../../../config/database.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

/**
 * Enregistre un nouvel utilisateur administrateur.
 */
function registerUser($mail, $firstname, $lastname, $address, $password, $phone, $job, $birth_date) {
    $conn = connectDb();
    $sql = "INSERT INTO users (mail, firstname, lastname, address, password, phone, job, birth_date, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt->bind_param("ssssssss", $mail, $firstname, $lastname, $address, $hashedPassword, $phone, $job, $birth_date);

    if ($stmt->execute()) {
        return [
            'status' => 'success',
            'message' => 'Registration successful'
        ];
    } else {
        return [
            'status' => 'error',
            'message' => 'Registration failed: ' . $stmt->error
        ];
    }
}

// Traiter la requête POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $mail = $data['mail'] ?? '';
    $firstname = $data['firstname'] ?? '';
    $lastname = $data['lastname'] ?? '';
    $address = $data['address'] ?? '';
    $password = $data['password'] ?? '';
    $phone = $data['phone'] ?? '';
    $job = $data['job'] ?? '';
    $birth_date = $data['birth_date'] ?? '';

    if (empty($mail) || empty($firstname) || empty($lastname) || empty($address) || empty($password) || empty($phone) || empty($job) || empty($birth_date)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
    } else {
        $result = registerUser($mail, $firstname, $lastname, $address, $password, $phone, $job, $birth_date);
        echo json_encode($result);
    }
}
?>
