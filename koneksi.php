<?php
$hostname = "localhost";
$database = "kmmi-blog";
$username = "root";
$password = "root";

$connect = mysqli_connect($hostname, $username, $password, $database);

if(!$connect){
    die("Koneksi tidak berhasil : " . mysqli_connect_error());
}
