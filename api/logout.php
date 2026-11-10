<?php
session_start();
$_SESSION = [];
session_destroy();
header('Location: ../frontend/front-end/index.html');
exit;
