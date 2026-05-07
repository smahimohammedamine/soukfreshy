<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!empty($_SESSION['admin_id'])) {
    echo json_encode(['success' => true, 'username' => $_SESSION['admin_name']]);
} else {
    http_response_code(401);
    echo json_encode(['success' => false]);
}