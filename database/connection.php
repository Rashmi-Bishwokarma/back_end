<?php

$HOST = 'localhost';
$USER = 'root';
$PASS = '';
$DB = 'final_fyp_database';

$CON = mysqli_connect($HOST, $USER, $PASS, $DB);

if (!$CON) {

    echo 'Connection failed';
}
