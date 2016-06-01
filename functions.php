<?php
function connect_db(){
	global $connection;
	$host="localhost";
	$user="test";
	$pass="t3st3r123";
	$db="test";
	$connection = mysqli_connect($host, $user, $pass, $db) or die("ei saa ühendust mootoriga- ".mysqli_error());
	mysqli_query($connection, "SET CHARACTER SET UTF8") or die("Ei saanud baasi utf-8-sse - ".mysqli_error($connection));
}
function login(){
	global $connection;
	if(!empty($_SESSION["user"])){
		header("Location: ?page=esileht");
	}else{
      	if($_SERVER['REQUEST_METHOD'] == 'POST'){
          if($_POST["user"] == '' || $_POST["password"] == ''){
  				      $errors =array();
  				if(empty($_POST["user"])) {
  				      $errors[] = "Palun sisesta kasutajanimi!";
  				}
  				if(empty($_POST["password"]))
  				      $errors[] = "Palun sisesta parool!";
        }else{
          $kasutaja = mysqli_real_escape_string ($connection, $_POST["user"]);
          $parool = mysqli_real_escape_string ($connection, $_POST["password"]);	
          $query = 'SELECT id, parool FROM mjaager_projekt_kasutajad WHERE kasutaja=? LIMIT 1';
          $stmt = mysqli_prepare($connection, $query);
              if(mysqli_error($connection)){
                echo mysqli_error($connection);
                exit;
              }			  
              mysqli_stmt_bind_param($stmt, 's', $kasutaja);
              mysqli_stmt_execute($stmt);
              mysqli_stmt_bind_result($stmt, $id, $hash);
              mysqli_stmt_fetch($stmt);
              mysqli_stmt_close($stmt);
              if(password_verify($parool, $hash)){
                $_SESSION["user"]= $kasutaja;
                session_regenerate_id();
                header('Location: ?page=esileht');
                exit;
              }else{
               echo "<script> alert('Vale parool'); </script>";
              }
            }
      }
  }	
	include('views/logisisse.html');
}
function logout(){
	$_SESSION = array();
	if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
	}
	session_destroy();
	header('Location: ?');
	exit;
}
function register(){
  global $connection;
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
      if($_POST["user"] == '' || $_POST["password"] == '' || $_POST["passwordagain"] == ''|| $_POST["asutus"] == ''|| $_POST["telefon"] == '' || $_POST["email"] == ''){
        $errors =array();
        if(empty($_POST["user"])) {
          $errors[] = "Sisesta kasutajanimi";
        }
        if(empty($_POST["password"])) {
          $errors[] = "Sisesta parool";
        }
        if(empty($_POST["passwordagain"])) {
          $errors[] = "Korda parooli!";
        }
        if(empty($_POST["asutus"])){
        $errors[] = "Sisesta ettevõtte nimi!";
        }
        if(empty($_POST["telefon"])){
        $errors[] = "Sisesta telefoninumber!";
        }
        if(empty($_POST["email"])) {
        $errors[] = "Sisesta e-mail!";
        }
    }else{
      if($_POST["password"] == $_POST["passwordagain"]){
      $kasutaja = mysqli_real_escape_string ($connection, $_POST["user"]);
      $parool = mysqli_real_escape_string ($connection, $_POST["password"]);
      $firma = mysqli_real_escape_string ($connection, $_POST["asutus"]);
      $telefon = mysqli_real_escape_string ($connection, $_POST["telefon"]);
      $email = mysqli_real_escape_string ($connection, $_POST["email"]);
      $hash = password_hash($parool, PASSWORD_DEFAULT);
      $query= 'INSERT INTO mjaager_projekt_kasutajad (kasutaja, parool, ettevote, telefon, email) VALUES (?,?,?,?,?)';
      $stmt = mysqli_prepare($connection, $query);
      mysqli_stmt_bind_param($stmt, 'sssss', $kasutaja, $hash, $firma, $telefon, $email);
      mysqli_stmt_execute($stmt);
	  
      $id = mysqli_insert_id($connection);
        if($id){
          header('Location: ?page=esileht');
          exit;
        }else{
          header('Location: ?page=registreerimine');
          exit;
        }
      mysqli_stmt_close($stmt);
    }else{      
	  $errors[]= "Paroolid ei kattu!";
    }
    }
  }
	include('views/registreerimine.html');
}
function lisadokument(){
	global $connection;
    if(empty($_SESSION['user'])){
		header('Location: ?');
		exit;
    }else{
      if($_SERVER['REQUEST_METHOD'] == 'POST'){
        if($_POST["dokumendinumber"] == '' || $_POST["dokumendinimetus"] == ''){
          $errors =array();
          if(empty($_POST["dokumendinumber"])){
          $errors[] = "Sisesta dokumendinumber";
          }
		  if(empty($_POST["dokumendinimetus"])) {
            $errors[] = "Sisesta dokumendinimetus";
          }          
      }else{
        $dokumendinumber = mysqli_real_escape_string ($connection, $_POST["dokumendinumber"]);
        $dokumendinimetus = mysqli_real_escape_string ($connection, $_POST["dokumendinimetus"]);        
		$kasutaja = $_SESSION['user'];
        $query= 'INSERT INTO merlenhe_projekt_dokumendid (dokumendinumber, dokumendinimetus, kasutaja) VALUES (?,?,?)';
        $stmt = mysqli_prepare($connection, $query);
        mysqli_stmt_bind_param($stmt, 'sss', $dokumendinumber, $dokumendinimetus, $kasutaja);
        mysqli_stmt_execute($stmt);
        $id = mysqli_insert_id($connection);
        if($id){
		   header('Location: ?page=sisestadokument');
        }else{
          header('Location: ?page=esileht');
          exit;
        }
      mysqli_stmt_close($stmt);
      }
}
}	
	include('views/sisestadokument.html');
}
function kuvadokumendid() {
  global $connection;
  if(empty($_SESSION['user'])){
    header('Location: ?');
    exit;
  }else{
  $kasutaja = mysqli_real_escape_string ($connection, $_SESSION["user"]);
  $query = 'SELECT id, dokumendinumber, dokumendinimetus FROM merlenhe_projekt_dokumendid WHERE kasutaja=?';
  $stmt = mysqli_prepare($connection, $query);
  if(mysqli_error($connection)){
	echo mysqli_error($connection);
	exit;
  }  
  mysqli_stmt_bind_param($stmt, 's', $kasutaja);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_bind_result($stmt, $id, $dokumendinumber, $dokumendinimetus);  
  $rows = array();
  while (mysqli_stmt_fetch($stmt)) {
        $rows[]= array(
          'id' => $id,
          'dokumendinumber' => $dokumendinumber,
          'dokumendinimetus' => $dokumendinimetus,          
        );
    }
	return $rows;
  }
}
function kustutadokument($id) {
	global $connection;
	if(empty($_SESSION['user'])){
		header('Location: ?');
		exit;
	}else{
	    $query = 'DELETE FROM merlenhe_projekt_dokumendid WHERE id=? LIMIT 1';
        $stmt = mysqli_prepare($connection, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $deleted = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
		if($deleted){
			header('Location: ?page=dokumendibaas');
			exit;
        }else {
			header('Location: ?');
        }
    }
}
?>