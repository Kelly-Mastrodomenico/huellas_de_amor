<?php
// ============================================================
// logout.php — Cerrar sesion
// Destruye la sesion y redirige al inicio
// ============================================================

session_start();
session_destroy();

header('Location: index.php');
exit();
?>
