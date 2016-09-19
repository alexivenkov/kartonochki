<?php

$id = $_GET['id'];

session_start();
echo $_SESSION['print'][$id];
unset($_SESSION['print'][$id]);