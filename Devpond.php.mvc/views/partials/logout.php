<?php
$_SESSION['user_id'] = null;
$_SESSION['username'] = null;
$_SESSION['firstname'] = null;
$_SESSION['lastname'] = null;
$_SESSION['user_role'] = null;
$_SESSION['user_logged_in'] = null;

session_destroy();
header("Location: /Index");
?>