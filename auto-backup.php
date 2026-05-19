<?php
set_time_limit(300);

$host = "sql310.infinityfree.com";
$user = "if0_41890353";
$pass = "FinalProject001"; 
$dbname = "if0_41890353_pc_builder_db";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$tables = [];
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_row()) {
    $tables[] = $row[0];
}

$sql_backup = "-- Synthesis PC Architecture Database Backup\n";
$sql_backup .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
$sql_backup .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

foreach ($tables as $table) {
  
    $res_structure = $conn->query("SHOW CREATE TABLE `$table`");
    $row_structure = $res_structure->fetch_row();
    $sql_backup .= "\n\n" . $row_structure[1] . ";\n\n";
    
    $res_data = $conn->query("SELECT * FROM `$table`");
    while ($row = $res_data->fetch_assoc()) {
        $fields = array_keys($row);
        $escaped_values = array_map(function($value) use ($conn) {
            if ($value === null) return 'NULL';
            return "'" . $conn->real_escape_string($value) . "'";
        }, array_values($row));
        
        $sql_backup .= "INSERT INTO `$table` (`" . implode("`, `", $fields) . "`) VALUES (" . implode(", ", $escaped_values) . ");\n";
    }
}

$sql_backup .= "\n\nSET FOREIGN_KEY_CHECKS=1;\n";

$backup_folder = __DIR__ . '/backups/';
if (!is_dir($backup_folder)) {
    mkdir($backup_folder, 0755, true);
}

$file_name = 'backup_' . $dbname . '_' . date('Ymd_His') . '.sql';
$file_path = $backup_folder . $file_name;

if (file_put_contents($file_path, $sql_backup)) {
    echo "Backup successfully created: " . $file_name;
    
} else {
    echo "Error: Unable to save backup file.";
}

$conn->close();
?>
