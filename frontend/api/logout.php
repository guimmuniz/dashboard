<?php
session_start();
$_SESSION = [];
session_destroy();
header('Location: ../front-end/index.html');
exit;
