<?php
// This file's only purpose is to be loaded,
// which triggers the session update logic in auth.php

include('auth.php');

// Send a success response
header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'message' => 'Session updated.']);
?>