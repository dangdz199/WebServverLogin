<?php
//api url filter
if(strpos($_SERVER['REQUEST_URI'],"DB.php")){
    require_once 'Utils.php';
    PlainDie();
}

$conn = new mysqli("localhost", "root", 'new_$!9K#2@8&5*3%7(6)4^1_0+AbCdEfGhIjKlMnOpQrStUvWxYzXyZ', "danglmao");
if($conn->connect_error != null){
    die($conn->connect_error);
}