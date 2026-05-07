<?php
if(session_status()===PHP_SESSION_NONE) session_start();
require_once __DIR__.'/db.php';

function current_user_id(){ return $_SESSION['user_id'] ?? null; }
function current_user(){
  if(!current_user_id()) return null;
  $st = db()->prepare('SELECT user_id,email,full_name FROM user WHERE user_id=?');
  $st->execute([current_user_id()]);
  return $st->fetch() ?: null;
}
function require_login($redirect='login.php'){
  if(!current_user_id()){
    $next = urlencode($_SERVER['REQUEST_URI'] ?? 'index.php');
    header("Location: $redirect?next=$next"); exit;
  }
}
function login_user($email,$password){
  $st = db()->prepare('SELECT user_id,password_hash FROM user WHERE email=?');
  $st->execute([$email]);
  $row = $st->fetch();
  if($row && password_verify($password,$row['password_hash'])){
    $_SESSION['user_id'] = (int)$row['user_id'];
    return true;
  }
  return false;
}
function register_user($email,$password,$name){
  $hash = password_hash($password, PASSWORD_BCRYPT);
  $st = db()->prepare('INSERT INTO user (email,password_hash,full_name) VALUES (?,?,?)');
  $st->execute([$email,$hash,$name]);
  $_SESSION['user_id'] = (int)db()->lastInsertId();
}
function logout_user(){ $_SESSION = []; session_destroy(); }

// admin
function current_admin_id(){ return $_SESSION['admin_id'] ?? null; }
function require_admin(){
  if(!current_admin_id()){ header('Location: index.php'); exit; }
}
function admin_login($username,$password){
  $st = db()->prepare('SELECT admin_id,password_hash FROM admin_user WHERE username=?');
  $st->execute([$username]);
  $row = $st->fetch();
  if($row && password_verify($password,$row['password_hash'])){
    $_SESSION['admin_id'] = (int)$row['admin_id']; return true;
  }
  return false;
}
function admin_logout(){ unset($_SESSION['admin_id']); }
