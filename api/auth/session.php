<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!empty($_SESSION['user_id'])) {
    echo json_encode([
        'logged_in' => true,
        'user' => [
            'id'        => $_SESSION['user_id'],
            'full_name' => $_SESSION['full_name'],
            'role'      => $_SESSION['role'],
            'phone'     => $_SESSION['phone'] ?? '',
            'email'     => $_SESSION['email'] ?? '',
            'wilaya'    => $_SESSION['wilaya'] ?? '',
            'commune'   => $_SESSION['commune'] ?? '',
        ],
    ]);
} else {
    echo json_encode(['logged_in' => false]);
}