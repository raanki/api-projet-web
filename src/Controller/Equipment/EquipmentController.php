<?php

require_once '../../../config/cors.php';
require_once '../../../config/database.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);



/**
 * retourne un json de tous les équipements de la bdd
 */
function getEquipments($filter = '', $order = 'ASC') {
    $conn = connectDb();
    $sql = "SELECT * FROM equipment";

    // Appliquer un filtre si présent
    if (!empty($filter)) {
        $sql .= " WHERE " . $filter;
    }

    // Appliquer l'ordre si présent
    if (!empty($order)) {
        $sql .= " ORDER BY name $order";
    } else {
        $sql .= " ORDER BY name $order";
    }

    $result = $conn->query($sql);
    $equipments = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $equipments[] = $row;
        }
        $result->free();
    }

    $conn->close();
    return $equipments;
}

/**
 * Crée un nouvel équipement dans la base de données
 */
function createEquipment($data) {
    $conn = connectDb();
    $sql = "INSERT INTO equipment (name, description, purchase_date, purchase_price, supplier, availability) VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssds", $data['name'], $data['description'], $data['purchaseDate'], $data['purchasePrice'], $data['supplier'], $data['availability']);

    if ($stmt->execute()) {
        $last_id = $stmt->insert_id;
        $stmt->close();
        $conn->close();
        return '';
    } else {
        $stmt->close();
        $conn->close();
        return ['error' => 'Unable to create equipment.'];
    }
}



// Traiter la requête POST
if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? '';
    $filter = $_POST['filter'] ?? '';
    $order = $_POST['order'] ?? '';

    if ($action == 'POST' && empty($id) && empty($filter)) {
        echo json_encode(['error' => 'No valid parameters provided for the query.']);
    } else {
        $result = getEquipments($filter, $order);
        echo json_encode($result);
    }
}

// Traiter la requête POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    $action = $data['action'] ?? '';

    if ($action == 'create') {
        $newEquipment = createEquipment($data);
        echo json_encode($newEquipment);
    }

}