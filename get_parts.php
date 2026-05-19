<?php
ob_clean(); 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

$host = "sql310.infinityfree.com";
$user = "if0_41890353";
$pass = "FinalProject001"; 
$dbname = "if0_41890353_pc_builder_db";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connection failed"]);
    exit();
}

$conn->set_charset("utf8mb4");

$sql = "SELECT category, name, price, brand, release_year, performance_tag FROM parts WHERE stock > 0";
$result = $conn->query($sql);

$parts = [];

if ($result) {
    while($row = $result->fetch_assoc()) {
        $row['price'] = (float)$row['price'];
        $row['release_year'] = isset($row['release_year']) ? (int)$row['release_year'] : 2022;
        $row['performance_tag'] = isset($row['performance_tag']) ? $row['performance_tag'] : 'general';
        $parts[] = $row;
    }
    echo json_encode($parts);
} else {
    $sql_fallback = "SELECT category, name, price, brand FROM parts WHERE stock > 0";
    $res_fallback = $conn->query($sql_fallback);
    $parts_fallback = [];
    while($r = $res_fallback->fetch_assoc()) {
        $r['price'] = (float)$r['price'];
        $parts_fallback[] = $r;
    }
    echo json_encode($parts_fallback);
}

$conn->close();
