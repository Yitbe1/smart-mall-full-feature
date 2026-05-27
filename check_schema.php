<?php
require_once 'includes/db.php';
$pdo = getDB();
$columns = $pdo->query("DESCRIBE products")->fetchAll(PDO::FETCH_COLUMN);
echo json_encode($columns);
?>
