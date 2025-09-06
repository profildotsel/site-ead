<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function requireRole($required_role) {
    requireLogin();
    if ($_SESSION['user_role'] !== $required_role) {
        header("Location: access_denied.php");
        exit();
    }
}

function requireRoles($required_roles) {
    requireLogin();
    if (!in_array($_SESSION['user_role'], $required_roles)) {
        header("Location: access_denied.php");
        exit();
    }
}

function isAdmin() {
    return isLoggedIn() && $_SESSION['user_role'] === 'administrador';
}

function isInstructor() {
    return isLoggedIn() && $_SESSION['user_role'] === 'instrutor';
}

function isStudent() {
    return isLoggedIn() && $_SESSION['user_role'] === 'estudante';
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUserRole() {
    return $_SESSION['user_role'] ?? null;
}

function getCurrentUserName() {
    return $_SESSION['user_name'] ?? '';
}
?>