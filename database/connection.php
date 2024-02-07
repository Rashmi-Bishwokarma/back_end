<?php

$HOST = 'localhost';
$USER = 'root';
$PASS = '';
$DB = 'doctor_appointment';

$CON = mysqli_connect($HOST, $USER, $PASS, $DB);

if (!$CON) {

    echo 'Connection failed';
}
