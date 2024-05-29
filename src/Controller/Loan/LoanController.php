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

// Traiter la requête GET
if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    $action = $_GET['action'] ?? '';
    $id = $_GET['id'] ?? '';
    $filter = $_GET['filter'] ?? '';
    $order = $_GET['order'] ?? '';

    if ($action == 'GET' && empty($id) && empty($filter)) {
        echo json_encode(['error' => 'No valid parameters provided for the query.']);
    } else {
        $result = getLoans($filter, $order);
        echo json_encode($result);
    }
}

