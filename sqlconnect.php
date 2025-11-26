<?php
    $username = "root";
    $password = "";
    $database = "dbpharmascan";

    $conn = mysqli_connect("localhost" , $username , $password) or die ("Unable to Connect Database");
    mysqli_select_db($conn , $database) or die ("Unable to Select Database");

