<?php
require_once '../../../config/cors.php';
require_once '../../../config/database.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

/**
 * Retourne un JSON de tous les prêts de la BDD
 */
function getLoans($filter = '', $order = 'ASC') {
    $conn = connectDb();
    $sql = "SELECT * FROM to_loan";

    // Appliquer un filtre si présent
    if (!empty($filter)) {
        $sql .= " WHERE " . $filter;
    }

    // Appliquer l'ordre si présent
    if (!empty($order)) {
        $sql .= " ORDER BY start_date $order";
    } else {
        $sql .= " ORDER BY start_date $order";
    }

    $result = $conn->query($sql);
    $loans = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $loans[] = $row;
        }
        $result->free();
    }

    $conn->close();
    return $loans;
}

function getLoanById($id) {
    $conn = connectDb();
    $sql = "SELECT * FROM to_loan WHERE loan_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $loan = $result->fetch_assoc();
        $stmt->close();
        $conn->close();
        return $loan;
    } else {
        $stmt->close();
        $conn->close();
        return null;
    }
}

function updateLoanById($id, $mail, $start_date, $expect_end_date, $actual_end_date, $commentary, $material_id) {
    $conn = connectDb();

    $sql = "UPDATE to_loan SET
            mail = ?,
            start_date = ?,
            expect_end_date = ?,
            actual_end_date = ?,
            commentary = ?,
            material_id = ?
        WHERE loan_id = ?";

    // Préparation de la requête
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $mail, $start_date, $expect_end_date, $actual_end_date, $commentary, $material_id, $id);

    $success = $stmt->execute();

    $stmt->close();
    $conn->close();

    return $success;
}

function createLoan($mail, $start_date, $expect_end_date, $actual_end_date, $commentary, $material_id) {
    $conn = connectDb();
    $createdAt = date("Y-m-d H:i:s");

    $sql = "INSERT INTO to_loan (mail, start_date, expect_end_date, actual_end_date, commentary, material_id, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    // Préparation de la requête
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssis", $mail, $start_date, $expect_end_date, $actual_end_date, $commentary, $material_id, $createdAt);
    $success = $stmt->execute();

    $stmt->close();
    $conn->close();

    return $success;
}

function deleteLoan($loan_id) {
    $conn = connectDb();
    $sql = "DELETE FROM to_loan WHERE loan_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $loan_id);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();

    return $success;
}

// Traiter les requêtes GET et POST
if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    $action = $_GET['action'] ?? '';
    $id = $_GET['id'] ?? '';
    $filter = $_GET['filter'] ?? '';
    $order = $_GET['order'] ?? '';
var_dump($action);
    if ($action === 'list') {
        $result = getLoans($filter, $order);
        echo json_encode($result);
    } elseif ($action === 'view' && !empty($id)) {
        $loan = getLoanById($id);
        if ($loan) {
            echo json_encode($loan);
        } else {
            echo json_encode(['error' => 'Loan not found.']);
        }
    } else {
        echo json_encode(['error' => 'Invalid action or parameters.']);
    }

} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Traiter les requêtes POST pour créer ou mettre à jour un prêt
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $mail = $_POST['mail'];
        $start_date = $_POST['start_date'];
        $expect_end_date = $_POST['expect_end_date'];
        $actual_end_date = $_POST['actual_end_date'];
        $commentary = $_POST['commentary'];
        $material_id = $_POST['material_id'];

        $success = createLoan($mail, $start_date, $expect_end_date, $actual_end_date, $commentary, $material_id);
        echo json_encode(['success' => $success]);

    } elseif ($action === 'update' && !empty($_POST['id'])) {
        $id = $_POST['id'];
        $mail = $_POST['mail'];
        $start_date = $_POST['start_date'];
        $expect_end_date = $_POST['expect_end_date'];
        $actual_end_date = $_POST['actual_end_date'];
        $commentary = $_POST['commentary'];
        $material_id = $_POST['material_id'];

        $success = updateLoanById($id, $mail, $start_date, $expect_end_date, $actual_end_date, $commentary, $material_id);
        echo json_encode(['success' => $success]);
    } else {
        echo json_encode(['error' => 'Invalid action or parameters.']);
    }
}

