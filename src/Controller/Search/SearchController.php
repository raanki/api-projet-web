<?php
require_once '../../../config/cors.php';
require_once '../../../config/database.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

function searchAllTables($searchTerm) {
    $conn = connectDb();
    $results = [];

    // Préparer le terme de recherche pour une recherche partielle
    $likeTerm = '%' . $searchTerm . '%';

    // Rechercher dans la table users
    $sqlUsers = "SELECT 'users' AS table_name, mail, student_number, firstname, lastname, address, password, phone, job, birth_date, created_at 
                 FROM users 
                 WHERE mail LIKE ? OR
                       firstname LIKE ? OR
                       lastname LIKE ? OR
                       address LIKE ? OR
                       phone LIKE ? OR
                       job LIKE ?";
    $stmtUsers = $conn->prepare($sqlUsers);
    $stmtUsers->bind_param("ssssss", $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm);
    $stmtUsers->execute();
    $resultUsers = $stmtUsers->get_result();
    while ($row = $resultUsers->fetch_assoc()) {
        $results[] = $row;
    }
    $stmtUsers->close();

    // Rechercher dans la table image
    $sqlImage = "SELECT 'image' AS table_name, image_id, description, mail 
                 FROM image 
                 WHERE description LIKE ? OR mail LIKE ?";
    $stmtImage = $conn->prepare($sqlImage);
    $stmtImage->bind_param("ss", $likeTerm, $likeTerm);
    $stmtImage->execute();
    $resultImage = $stmtImage->get_result();
    while ($row = $resultImage->fetch_assoc()) {
        $results[] = $row;
    }
    $stmtImage->close();

    // Rechercher dans la table to_loan
    $sqlLoan = "SELECT 'to_loan' AS table_name, loan_id, start_date, expect_end_date, commentary, created_at, mail, material_id, actual_end_date 
                FROM to_loan 
                WHERE commentary LIKE ? OR mail LIKE ?";
    $stmtLoan = $conn->prepare($sqlLoan);
    $stmtLoan->bind_param("ss", $likeTerm, $likeTerm);
    $stmtLoan->execute();
    $resultLoan = $stmtLoan->get_result();
    while ($row = $resultLoan->fetch_assoc()) {
        $results[] = $row;
    }
    $stmtLoan->close();

    // Rechercher dans la table equipment
    $sqlEquipment = "SELECT 'equipment' AS table_name, material_id, name, barcode, description, purchase_date, purchase_price, supplier, status, availability, created_at, image_id 
                     FROM equipment 
                     WHERE name LIKE ? OR
                           barcode LIKE ? OR
                           description LIKE ? OR
                           supplier LIKE ? OR
                           status LIKE ?";
    $stmtEquipment = $conn->prepare($sqlEquipment);
    $stmtEquipment->bind_param("sssss", $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm);
    $stmtEquipment->execute();
    $resultEquipment = $stmtEquipment->get_result();
    while ($row = $resultEquipment->fetch_assoc()) {
        $results[] = $row;
    }
    $stmtEquipment->close();

    $conn->close();
    return $results;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    $searchTerm = $_GET['search'] ?? '';

    if (!empty($searchTerm)) {
        $results = searchAllTables($searchTerm);
        echo json_encode($results);
    }
}
?>
