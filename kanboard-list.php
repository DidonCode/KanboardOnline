<?php
	
	session_start();

	require('bddConnect.php');

	if(!isset($_SESSION['identifier']) || !isset($_SESSION['id'])){
		header('Location: disconnect.php?auto=1.php');
	}

	if(isset($_GET['create'])){

		if(isset($_GET['kanboard_name'])){
			$name = $_SESSION['identifier']."_".$_GET['kanboard_name'];

			if(!empty($name)){

				$request = $pdo->prepare("SHOW TABLES FROM kanboard");
				$request->execute();
				$tableInfo = $request->fetchAll();
				$tableNumber = $request->rowCount();
				$tableExist = 0;

				for($i = 0; $i < $tableNumber; $i++){
					if($tableInfo[$i][0] == $name){
						$tableExist = 1;
					}
				}

				if($tableExist == 0){

					$request = $pdo->prepare("
						CREATE TABLE `".$name."` (
						`host` varchar(255) NOT NULL DEFAULT ''
						) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
					");
					$request->execute();

					$request = $pdo->prepare("INSERT INTO ".$name." (host) VALUES ('".$_SESSION['identifier']."')");
					$request->execute();

					$_SESSION['kanboard'] = $name;

					$name = $_SESSION['identifier']."_".$_GET['kanboard_name']."_colors";

					$request = $pdo->prepare("
						CREATE TABLE `$name` (
						`id` int(55) NOT NULL,
						`name` varchar(255) NOT NULL,
						`r` int(55) NOT NULL,
						`g` int(55) NOT NULL,
						`b` int(55) NOT NULL
						) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
					");
					$request->execute();

					$request = $pdo->prepare("ALTER TABLE `$name` ADD PRIMARY KEY (`id`);");
					$request->execute();

					$request = $pdo->prepare("ALTER TABLE `$name` MODIFY `id` int(55) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;");
					$request->execute();

					$request = $pdo->prepare("
						INSERT INTO `$name` (`id`, `name`, `r`, `g`, `b`) VALUES
						(1, 'rouge', 255, 0, 0),
						(2, 'vert', 0, 255, 0),
						(3, 'bleu', 0, 0, 255),
						(4, 'jaune', 255, 255, 0),
						(5, 'violet', 255, 0, 255);
					");
					$request->execute();

					header('Location: kanboard-dashboard.php');
				}else{
					header('Location: kanboard-list.php?error=1');
				}

			}else{
				header('Location: kanboard-list.php?error=2');
			}
		}

	}

	if(isset($_GET['name'])){
		$_SESSION['kanboard'] = $_GET['name'];
		header('Location: kanboard-dashboard.php');
	}

	if(isset($_GET['delete'])){
		$name = $_GET['delete'];
		$request = $pdo->prepare("DROP TABLE $name");
		$request->execute();

		$name = $_GET['delete']."_colors";
		$request = $pdo->prepare("DROP TABLE $name");
		$request->execute();

		header('Location: kanboard-list.php');
	}

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="shortcut icon" href="logo_b.ico">
		<link rel="stylesheet" type="text/css" href="css/all.css">
		<link rel="stylesheet" type="text/css" href="css/popup.css">
		<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

		<title>Kanboard Online</title>
	</head>

	<body>
		<header style="height: 60px;">
			<div style="width: 100%">
				<div style="width: 98%">
					<div style="float: left;">
						<h2 style="margin-left: 35px; text-decoration: underline;">Kanboard Online</h2>
					</div>

					<div style="float: right; margin-top: 5px;">
						<div class="icon-inline" style="padding-right: 40px;">
							<i class="fa fa-plus"></i>
							<a><h4 id="popup-open">Ajouter</h4></a>
						</div>

						<div class="icon-inline">
							<i class="fa fa-power-off"></i> 
							<a href="disconnect.php"><h4>Deconnexion</h4></a>
						</div>
					</div>
				</div>
			</div>
		</header>

		<div style="margin-top: 6em;"></div>

		<div id="popup" class="popup">
			<div class="popup-content">
				<span class="popup-close" style="margin-left: 2px;">&times;</span>
				<form method="GET" autocomplete="off">
					<br>
					<input type="text" name="kanboard_name" placeholder="Nom du kanboard" class="form-control">
					<br>
					<input type="submit" name="create" value="Crée" lass="form-control">
					<br>
					<?php 
						if(isset($_GET['error'])){
							if($_GET['error'] == 1) {$error = "Ce nom est déja utilisé";}
							if($_GET['error'] == 2) {$error = "Veuillez rentré un nom";}

							echo '<div align="center">';
							echo '<h4 style="color: red;">'.$error.'</h4>';
							echo '</div>';
						}
					?>
				</form>
			</div>
		</div>

		<section>
			<div align="center">
				<div style="border: 2px solid white; width: fit-content; padding: 35px;">
					<div class="icon-inline">
						<i class="fa fa-clipboard-list"></i>
						<h3> Kanboard</h3>
					</div>

					<div style="padding-top: 5px;"></div>

					<table>
						<?php
							$request = $pdo->prepare("SHOW TABLES FROM kanboard");
							$request->execute();
							$tableInfo = $request->fetchAll();
							$tableNumber = $request->rowCount();

							$exist = 0;

							if($tableNumber > 0){
								for($i = 0; $i < $tableNumber; $i++){
									if($tableInfo[$i][0] != "users" && !strpos($tableInfo[$i][0], 'colors') && $tableInfo[$i][0] != "colors"){
										$tableName = $tableInfo[$i][0];
										$table = explode("_", $tableName);

										$request = $pdo->prepare("SELECT * FROM $tableName WHERE host = ?");
										$request->execute(array($_SESSION['identifier']));
										$kanboardInfo = $request->fetch();
										$kanboardNumber = $request->rowCount();

										if($kanboardNumber > 0){
											$exist++;

											echo '<tr>';
											echo '<td style="color: white; padding-right: 50px;"><a href="kanboard-list.php?name='.$tableName.'">'.$table[1].'</a></td>';
											echo '<td><a href="kanboard-list.php?delete='.$tableName.'"><i class="fa fa-trash" style="color: red;"></i></a></td>';
											echo '</tr>';
										}
									}
								}
							}

							if($exist == 0){
								echo '<h4>Aucun kanboard</h4>';
							}

						?>
					</table>
				</div>	
			</div>
		</section>
	</body>
</html>

<script>

var errors = 0;
var popup = document.getElementById("popup");
var popupOpen = document.getElementById("popup-open");
var popupClose = document.getElementsByClassName("popup-close")[0];

function open(){
	popup.style.display = "block";
}

function close(){
	if(errors > 0){
		document.location.href="kanboard-list.php";
	}
	popup.style.display = "none";
}

window.onclick = function(event) {
	if (event.target == popup) {
		close();
	}
}

popupOpen.onclick = function() {
	open();
}

popupClose.onclick = function() {
	close();
}

function error(r){
	errors = r;
}

</script>

<?php
	if(isset($_GET['error'])){
		echo '<script>open(); error('.$_GET['error'].');</script>';
	}
?>

<style>
	td{
		padding: 10px;
	}
</style>