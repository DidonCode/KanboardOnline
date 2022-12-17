<?php
	
	session_start();
	
	require('bddConnect.php');

	if(isset($_GET['login'])){
		if(isset($_GET['identifier']) && isset($_GET['password'])){
			$identifier = $_GET['identifier'];
			$password = $_GET['password'];

			if(!empty($identifier)){

				if(!empty($password)){

					$request = $pdo->prepare("SELECT * FROM users WHERE identifier = ? AND password = ?");
					$request->execute(array($identifier, $password));

					$usersInfo = $request->fetch();
					$usersNum = $request->rowCount();

					if($usersNum == 1){

						$_SESSION['id'] = $usersInfo['id'];
						$_SESSION['identifier'] = $usersInfo['identifier'];

						$request = $pdo->prepare("UPDATE users SET online = 1 WHERE id = ?");
						$request->execute(array($_SESSION['id']));

						header('Location: kanboard-list.php');

					}else{
						header('Location: index.php?error=3');	
					}	

				}else{
					header('Location: index.php?error=2');
				}

			}else{
				header('Location: index.php?error=1');
			}
		}
	}

?>

<!DOCTYPE html>
<html style="height: 90%;">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="shortcut icon" href="logo_b.ico">
		<link rel="stylesheet" type="text/css" href="css/all.css">
		<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

		<title>Kanboard Online</title>
	</head>

	<body style="height: 90%;">
		<header>
			<h2 align="left" style="margin-left: 35px; text-decoration: underline;">Kanboard Online</h2>	
		</header>

		<div style="margin-top: 6em;"></div>

		<section>
			<div align="center">
				<div style="border: 2px solid white; width: fit-content; padding: 35px;">
					<div class="icon-inline">
						<i class="fa fa-user"></i>
						<h3> Compte</h3>
					</div>

					<form method="GET">
						<input type="text" name="identifier" placeholder="Identifiant" class="form-control" id="identifier" autocomplete="off">
						<br>
						<input type="password" name="password" placeholder="Mot de passe" class="form-control" id="password" autocomplete="off">
						<br><br>
						<input type="submit" name="login" value="Connexion" class="form-control">
					</form>
				</div>	
			</div>
		</section>
	</body>
</html>

<script>
	function error(r){

		if(r == 1){
			var element = document.getElementById("identifier");
			element.setAttribute("id", "input-error");
		}

		if(r == 2){
			var element = document.getElementById("password");
			element.setAttribute("id", "input-error");
		}

		if(r == 3){
			var element = document.getElementById("identifier");
			element.setAttribute("id", "input-error");
			element = document.getElementById("password");
			element.setAttribute("id", "input-error");
		}

	}
</script>

<?php

	if(isset($_GET['error'])){
		$error = $_GET['error'];

		if(!empty($error)){
			
			echo '<script>error('.$error.');</script>';

		}
	}

?>