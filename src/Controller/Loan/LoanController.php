<?php

require_once '../../../config/cors.php';
require_once '../../../config/database.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

/**
 * Retourne un JSON de tous les prêts de la bdd
 */
function getLoans($filter = '', $order = 'ASC') {
    $conn = connectDb();
    $sql = "SELECT * FROM to_loan";

    if (!empty($filter)) {
        $sql .= " WHERE " . $filter;
    }

    if (!empty($order)) {
        $sql .= " ORDER BY loan_id $order";
    } else {
        $sql .= " ORDER BY loan_id $order";
    }

    $result = $conn->query($sql);
    $loans = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $row["commentary"] = ucfirst($row["commentary"]);
            $row["material_id"] = $row["material_id"] == "1" ? "Yes" : "No";
            $loans[] = $row;
        }
        $result->free();
    }

    $conn->close();
    return $loans;
}

/**
 * Crée un nouveau prêt dans la base de données
 */
function createLoan($data) {
    $conn = connectDb();
    $sql = "INSERT INTO to_loan (mail, loan_id, start_date, expect_end_date, actual_end_date, commentary, created_at, material_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisssssi", $data['mail'], $data['loan_id'], $data['start_date'], $data['expect_end_date'], $data['actual_end_date'], $data['commentary'], $data['created_at'], $data['material_id']);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        return '';
    } else {
        $stmt->close();
        $conn->close();
        return ['error' => 'Unable to create loan.'];
    }
}

/**
 * Met à jour un prêt existant
 */
function updateLoan($data) {
    $conn = connectDb();
    $sql = "UPDATE to_loan SET mail = ?, start_date = ?, expect_end_date = ?, actual_end_date = ?, commentary = ?, material_id = ? WHERE loan_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $data['mail'], $data['start_date'], $data['expect_end_date'], $data['actual_end_date'], $data['commentary'], $data['material_id'], $data['loan_id']);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        return '';
    } else {
        $stmt->close();
        $conn->close();
        return ['error' => 'Unable to update loan.'];
    }
}

/**
 * Récupère un prêt par ID
 */
function getLoanById($id) {
    $conn = connectDb();
    $sql = "SELECT * FROM to_loan WHERE loan_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $loan = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $loan;
}

/**
 * Supprime un prêt par ID
 */
function deleteLoan($id) {
    $conn = connectDb();
    $sql = "DELETE FROM to_loan WHERE loan_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}

// Traiter les requêtes
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    $action = $_GET['action'] ?? '';
    $id = $_GET['id'] ?? '';
    $filter = $_GET['filter'] ?? '';
    $order = $_GET['order'] ?? '';

    if ($action == 'fetch' && !empty($id)) {
        $loan = getLoanById($id);
        echo json_encode($loan);
    } else {
        $result = getLoans($filter, $order);
        echo json_encode($result);
    }
} elseif ($method == 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    $action = $data['action'] ?? '';

    if ($action == "fetch") {
        echo json_encode(getLoanById($data['id']));
    } elseif ($action == 'create') {
        $newLoan = createLoan($data);
        echo json_encode($newLoan);
    } elseif ($action == 'update') {
        $updatedLoan = updateLoan($data);
        echo json_encode($updatedLoan);
    }
} elseif ($method == 'DELETE') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    $id = $data['id'] ?? null;

    if ($id) {
        $success = deleteLoan($id);
        if ($success) {
            echo json_encode(['success' => 'Loan deleted']);
        } else {
            echo json_encode(['error' => 'Unable to delete loan']);
        }
    } else {
        echo json_encode(['error' => 'No ID provided']);
    }
}
?>
