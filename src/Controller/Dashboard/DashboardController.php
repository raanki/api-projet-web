<?php

require_once '../../../config/cors.php';
require_once '../../../config/database.php';

function getDashboardData() {
    $conn = connectDb();
    $data = [];

    // Nombre d'étudiants (utilisateurs avec un numéro d'étudiant)
    $sql = "SELECT COUNT(*) AS count FROM users WHERE student_number IS NOT NULL";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $data['numberOfStudents'] = $row['count'];

    // Prêts en retard (prêts avec une date de fin attendue dépassée)
    $sql = "SELECT COUNT(*) AS count FROM to_loan WHERE expect_end_date < CURDATE()";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $data['lateLoans'] = $row['count'];

    // Derniers prêts d'équipement
    $sql = "SELECT * FROM to_loan ORDER BY start_date DESC LIMIT 5";
    $result = $conn->query($sql);
    $data['latestEquipmentLoans'] = [];
    while ($row = $result->fetch_assoc()) {
        $data['latestEquipmentLoans'][] = $row;
    }

    // Nombre total d'équipements
    $sql = "SELECT COUNT(*) AS count FROM equipment";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $data['totalEquipment'] = $row['count'];

    // Prêts de la semaine (prêts créés au cours de la semaine en cours)
    $sql = "SELECT * FROM to_loan WHERE start_date >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)";
    $result = $conn->query($sql);
    $data['weekLoans'] = [];
    while ($row = $result->fetch_assoc()) {
        $data['weekLoans'][] = $row;
    }

// Prêts d'équipement les plus populaires (par exemple, les équipements les plus empruntés)
    $sql = "SELECT * FROM to_loan GROUP BY material_id ORDER BY COUNT(*) DESC LIMIT 5";
    $result = $conn->query($sql);
    $data['popularEquipmentLoans'] = [];
    while ($row = $result->fetch_assoc()) {
        $data['popularEquipmentLoans'][] = $row;
    }

// Retourner les données du tableau de bord au format JSON
    echo json_encode($data);

}

// Traiter la requête GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    getDashboardData();
}

?>
