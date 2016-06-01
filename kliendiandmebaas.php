<?php
require_once('functions.php');
session_start();
connect_db();
$page="";
if (isset($_GET['page']) && $_GET['page']!=""){
	$page=htmlspecialchars($_GET['page']);
}
require_once('views/head.html');
switch($page){
	case "logisisse":
		login();
	break;
	case "registreerimine":
		register();
	break;
	case "sisestadokument":
		lisadokument();
	break;
	case "dokumendibaas":
		 include('views/dokumendibaas.html');
	break;
	case "logout":
		logout();
	break;
	case "kustutadokument":
		$id = intval($_POST['id']);
		kustutadokument($id);
    break;	
	case "seaded":
		kuvaseaded();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
		muudaseaded();
		}
	break;	
	default:
		include_once('views/esileht.html');
	break;
}
require_once('views/foot.html');
?>