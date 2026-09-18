<?php
require_once __DIR__ . '/../../Controllers/AuthController.php';
(new AuthController())->logout();
header('Location: index.php?p=Public/home');
exit;