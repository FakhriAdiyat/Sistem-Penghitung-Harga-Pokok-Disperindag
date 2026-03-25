<?php
session_start();
session_destroy();
require_once __DIR__ . '/../config/app.php';
header("Location: " . BASE_URL);
exit;
