<?php
session_start();
session_destroy();
header('Location: /sppd/pages/auth/login.php');
exit;
