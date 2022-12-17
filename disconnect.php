<?php
	
	require('bddConnect.php');

	session_start();

	$request = $pdo->prepare("UPDATE users SET online = 0 WHERE id = ?");
	$request->execute(array($_SESSION['id']));

	session_destroy();

	if(!isset($_GET['auto'])){
		header('Location: index.php');
	}

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" type="text/css" href="css/all.css">
		<link rel="stylesheet" type="text/css" href="css/popup.css">
		<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

		<title>Kanboard Online</title>
	</head>

	<body>
		<div id="popup1" class="popup" style="display: block;">
			<div class="popup-content">
				<h4 style="color: black;" align="center">Vous avez été deconnecté</h4>
				<form action="index.php">
					<input type="submit" name="" value="Retour a l'accueil">
				</form>
			</div>
		</div>
	</body>
</html>