<?php
$DB_HOST = 'localhost';
$DB_NAME = 'misaki';
$DB_USER = 'root';
$DB_PASS = '';

function db(){
  static $pdo = null;
  if($pdo) return $pdo;
  global $DB_HOST,$DB_NAME,$DB_USER,$DB_PASS;
  try{
    $pdo = new PDO(
      "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
      $DB_USER, $DB_PASS,
      [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
       PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
       PDO::ATTR_EMULATE_PREPARES=>false]
    );
  }catch(Throwable $e){
    die('<pre style="font:14px monospace;padding:24px">DB connection failed.<br>Make sure XAMPP MySQL is running and that you have imported <strong>sql/schema.sql</strong>.<br><br>'.htmlspecialchars($e->getMessage()).'</pre>');
  }
  return $pdo;
}