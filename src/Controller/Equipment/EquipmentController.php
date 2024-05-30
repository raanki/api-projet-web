<?php

require_once '../../../config/cors.php';
require_once '../../../config/database.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

/**
 * Retourne un JSON de tous les équipements de la bdd
 */
function getEquipments($filter = '', $order = 'ASC') {
    $conn = connectDb();
    $sql = "SELECT * FROM equipment";

    if (!empty($filter)) {
        $sql .= " WHERE " . $filter;
    }

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

    // Vérifier si 'availability' est définie, sinon la définir à 'false'
    $availability = isset($data['availability']) ? $data['availability'] : false;

    $stmt->bind_param("sssdss", $data['name'], $data['description'], $data['purchaseDate'], $data['purchasePrice'], $data['supplier'], $availability);

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

/**
 * Met à jour un équipement existant
 */
function updateEquipment($data) {
    $conn = connectDb();
    $sql = "UPDATE equipment SET name = ?, description = ?, purchase_date = ?, purchase_price = ?, supplier = ?, availability = ? WHERE material_id = ?";

    $stmt = $conn->prepare($sql);

    // Vérifier si 'availability' est définie, sinon la définir à 'false'
    $availability = isset($data['availability']) ? $data['availability'] : false;

    $stmt->bind_param("ssdsssi", $data['name'], $data['description'], $data['purchaseDate'], $data['purchasePrice'], $data['supplier'], $availability, $data['material_id']);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        return '';
    } else {
        $stmt->close();
        $conn->close();
        return ['error' => 'Unable to update equipment.'];
    }
}

/**
 * Récupère un équipement par ID
 */
function getEquipmentById($id) {
    $conn = connectDb();
    $sql = "SELECT * FROM equipment WHERE material_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $equipment = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $equipment;
}

/**
 * Supprime un équipement par ID
 */
function deleteEquipment($id) {
    $conn = connectDb();
    $sql = "DELETE FROM equipment WHERE material_id = ?";
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
        $equipment = getEquipmentById($id);
        echo json_encode($equipment);
    } else {
        $result = getEquipments($filter, $order);
        echo json_encode($result);
    }
} elseif ($method == 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    $action = $data['action'] ?? '';

    if ($action == 'fetch') {
        echo json_encode(getEquipmentById($data['id']));
    } elseif ($action == 'create') {
        $newEquipment = createEquipment($data);
        echo json_encode($newEquipment);
    } elseif ($action == 'update') {
        $updatedEquipment = updateEquipment($data);
        echo json_encode($updatedEquipment);
    }
} elseif ($method == 'DELETE') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    $id = $data['id'] ?? null;

    if ($id) {
        $success = deleteEquipment($id);
        if ($success) {
            echo json_encode(['success' => 'Equipment deleted']);
        } else {
            echo json_encode(['error' => 'Unable to delete equipment']);
        }
    } else {
        echo json_encode(['error' => 'No ID provided']);
    }
}
?>
