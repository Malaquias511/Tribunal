<?php


try{

$Connection = new PDO("mysql:host=192.185.2.185; dbname=admpfuxe_relatorios;port=3306", "admpfuxe_rel", "Corporategift$2019", array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));




}
catch(Exception $erro){

echo $erro;



}





?>