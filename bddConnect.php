<?php
	
	$pdo = null;
	$dsn = 'mysql: host=localhost; dbname=kanboard';
	$dbUser = 'root';
	$pw = '';

	try{
		$pdo = new PDO($dsn, $dbUser, $pw);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}catch (PDOException $e){
		echo 'Connection failed: ' . $e->getMessage();
	}

	return $pdo;
?>
