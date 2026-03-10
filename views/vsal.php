<?php
	session_start();
	if (isset($_SESSION['idemp']) && isset($_SESSION['idusu'])) {
		require_once('../models/conexion.php');
		require_once('../models/maud.php');
		$maud = new MAud();
		$maud->registrarLogout(
			$_SESSION['idemp'], 
			$_SESSION['idusu'], 
			$_SESSION['emausu'] ?? 'Desconocido', 
			$_SERVER['REMOTE_ADDR'], 
			$_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido'
		);
	}
	session_destroy();
	echo "<script>window.location='../index.php'</script>";
	exit();
?>