<?php
session_start();
session_destroy();
// After logout, redirect to the public index page
header('Location: index.html');
exit;
?>