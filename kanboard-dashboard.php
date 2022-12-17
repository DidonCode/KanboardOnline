<?php
	
	session_start();

	require('bddConnect.php');

	if(!isset($_SESSION['identifier']) || !isset($_SESSION['id'])){
		header('Location: disconnect.php?auto=1.php');
	}

	if(isset($_GET['add_column'])){
		$name = $_SESSION['kanboard'];
		$column = $_GET['column_name'];

		if(!empty($column)){

			$request = $pdo->prepare("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = ? AND column_name = ?");
			$request->execute(array($name, $column));
			$exist = $request->rowCount();

			if($exist == 0){

				$request = $pdo->prepare("ALTER TABLE $name ADD $column VARCHAR(255) NOT NULL DEFAULT '' ");
				$request->execute();

				header('Location: kanboard-dashboard.php');

			}else{
				header('Location: kanboard-dashboard.php?error=1');
			}
		}else{
			header('Location: kanboard-dashboard.php?error=2');
		}
	}

	if(isset($_GET['add_task'])){
		$name = $_SESSION['kanboard'];
		$column = $_SESSION['column'];

		$title = $_GET['title'];
		$description = $_GET['description'];
		$color = $_GET['color'];

		if(!empty($title) && !empty($description)){

			$request = $pdo->prepare("SELECT * FROM $name");
			$request->execute();
			$tableNumber = $request->rowCount();

			$data = $title." ::".$description." ::".$color;

			$request = $pdo->prepare("INSERT INTO $name ($column) VALUES ('$data')");
			$request->execute();

			header('Location: kanboard-dashboard.php');
		}else{
			header('Location: kanboard-dashboard.php?error=3');
		}
	}

	if(isset($_GET['mod_task'])){
		$name = $_SESSION['kanboard'];
		$column = $_SESSION['column'];
		$ligne = $_SESSION['ligne'];

		$title = $_GET['title'];
		$description = $_GET['description'];
		$color = $_GET['color'];

		if(!empty($title) && !empty($description)){

			$data = $title." ::".$description." ::".$color;

			$request = $pdo->prepare("UPDATE $name SET $column = '$data' WHERE $column = '$ligne'");
			$request->execute();

			header('Location: kanboard-dashboard.php');
		}

	}

	if(isset($_GET['delete'])){
		$name = $_SESSION['kanboard'];
		$columnName = $_GET['delete'];

		if(!empty($columnName)){

			$request = $pdo->prepare("ALTER TABLE $name DROP COLUMN $columnName");
			$request->execute();

			header('Location: kanboard-dashboard.php');

		}
	}

	if(isset($_GET['sup_task'])){
		$name = $_SESSION['kanboard'];
		$column = $_SESSION['column'];

		$title = $_GET['title'];
		$description = $_GET['description'];
		$color = $_GET['color'];

		$data = $title." ::".$description." ::".$color;

		$request = $pdo->prepare("DELETE FROM $name WHERE $column = '$data'");
		$request->execute();

		header('Location: kanboard-dashboard.php');

	}

	if(isset($_GET['add_partage'])){
		$name = $_SESSION['kanboard'];
		$user = $_GET['user'];

		$request = $pdo->prepare("INSERT INTO $name (host) VALUES ('$user')");
		$request->execute();

		header('Location: kanboard-dashboard.php');
	}

	if(isset($_GET['delete_partage'])){
		$name = $_SESSION['kanboard'];
		$user = $_GET['user'];

		$request = $pdo->prepare("DELETE FROM $name WHERE host = '$user'");
		$request->execute();

		header('Location: kanboard-dashboard.php');
	}

	if(isset($_GET['save_color'])){
		$name = $_SESSION['kanboard']."_colors";

		$request = $pdo->prepare("SELECT * FROM $name");
		$request->execute();
		$colorNumber = $request->rowCount();
		$colorData = $request->fetchAll();

		$fill = 0;
		for($i = 0; $i < $colorNumber; $i++){

			$colorName = $_GET['text_'.$i];

			if(empty($colorName)){
				$fill++;
			}

		}

		if($fill == 0){

			for($i = 0; $i < $colorNumber; $i++){

				list($r, $g, $b) = sscanf($_GET['color_'.$i], "#%02x%02x%02x");
				$colorName = $_GET['text_'.$i];
				$n = $i + 1;

				$request = $pdo->prepare("UPDATE $name SET r = '$r', g = '$g', b = '$b' WHERE id = '$n'");
				$request->execute();

				if($colorData[$i][1] != $colorName){

					$table = $_SESSION['kanboard'];

					$requestAll = $pdo->prepare("SELECT * FROM $table");
					$requestAll->execute();
					$tableNumber = $requestAll->rowCount();
					$tableInfo = $requestAll->fetchAll();
					$columnNumber = $requestAll->columnCount();

					for($j = 1; $j < $columnNumber; $j++){
						for($k = 1; $k < $tableNumber; $k++){
							
							$column = $requestAll->getColumnMeta($j);
							$columnName = $column['name'];

							if(!empty($tableInfo[$k][$j])){

								$ligne = $tableInfo[$k][$j];
								$ligneSplit = explode(" ::", $tableInfo[$k][$j]);
								$colorName1 = $ligneSplit[2];

								$request = $pdo->prepare("SELECT * FROM $name WHERE name = '$colorName1'");
								$request->execute();
								$colorData1 = $request->fetchAll();

								if($colorData[$i][0] == $colorData1[0][0]){

									$newLigne = $ligneSplit[0]." ::".$ligneSplit[1]." ::".$colorName;

									$request = $pdo->prepare("UPDATE $table SET $columnName = '$newLigne' WHERE $columnName = '$ligne'");
									$request->execute();

								}
							}
						}
					}

					$request = $pdo->prepare("UPDATE $name SET name = '$colorName' WHERE id = '$n'");
					$request->execute();
				}


			}

		}else {
			//header('Location: kanboard-dashboard.php?error=4');
		}

		//header('Location: kanboard-dashboard.php');

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
						<h2 style="padding-left: 35px; text-decoration: underline;">Kanboard Online</h2>
					</div>

					<div style="float: right; margin-top: 5px;">
						<div class="icon-inline" style="padding-right: 40px;">
							<i class="fa fa-arrow-left"></i>
							<a href="kanboard-list.php"><h4>Retour</h4></a>
						</div>

						<div class="icon-inline" style="padding-right: 40px;">
							<i class="fa-solid fa-palette"></i>
							<a><h4 onclick="open5();">Couleurs</h4></a>
						</div>

						<div class="icon-inline" style="padding-right: 40px;">
							<i class="fa-solid fa-share-nodes"></i>
							<a><h4 onclick="open4();">Partage</h4></a>
						</div>

						<div class="icon-inline" style="padding-right: 40px;">
							<i class="fa fa-minus"></i> 
							<?php
								if(isset($_GET['delete'])){
									echo '<a href="kanboard-dashboard.php"><h4>Enlever</h4></a>';
								}else{
									echo '<a href="kanboard-dashboard.php?delete="><h4>Enlever</h4></a>';
								}
							?>
						</div>

						<div class="icon-inline" style="padding-right: 40px;">
							<i class="fa fa-plus"></i> 
							<a><h4 onclick="open1();">Ajouter</h4></a>
						</div>

						<div class="icon-inline">
							<i class="fa fa-power-off"></i> 
							<a href="disconnect.php"><h4>Deconnexion</h4></a>
						</div>
					</div>
				</div>
			</div>
		</header>

		<div style="padding-top: 5px;"></div>

		<section style="height: 89%; position: absolute; width: 100%;">
			<div>
				<table style="display: inline-table; width: 100%;">
					<tr>
						<?php
							$name = $_SESSION['kanboard'];

							$request = $pdo->prepare("SELECT * FROM $name");
							$request->execute();
							$tableInfo = $request->fetchAll();
							$tableNumber = $request->rowCount();

							$columnNumber = $request->columnCount();

							for($i = 1; $i < $columnNumber; $i++){
								$columnInfo = $request->getColumnMeta($i);

								echo '<td class="task">';
								echo '<div style="width: 100%">';
								echo '<div style="width: 92%">';
								echo '<div style="float: left;">';
								echo '<h4>'.$columnInfo['name'].'</h4>';
								echo '</div>';
								echo '<div style="float: right;">';

								if(isset($_GET['delete'])){

									echo '<a href="kanboard-dashboard.php?delete='.$columnInfo['name'].'"><i class="fa fa-minus" style="color: black; padding-top: 23px;"></i></a>';

								}else{

									echo '<a href="kanboard-dashboard.php?column='.$columnInfo['name'].'"><i class="fa fa-plus" style="color: black; padding-top: 23px;"></i></a>';

								}
								echo '</div>';
								echo '</div>';
								echo '</div>';
								echo '</td>';
							}
						?>
					</tr>

					<?php

						$table = null;
						$ii = 0;
						$jj = 0;
						$jjmax = 0;

						for($i = 1; $i < $columnNumber; $i++){

							for($j = 1; $j < $tableNumber; $j++){

								if(!empty($tableInfo[$j][$i])){

									$table[$jj][$ii] = $tableInfo[$j][$i];
									$jj++;

								}

							}

							if($jj > $jjmax){
								$jjmax = $jj;
							}

							$jj = 0;
							$ii++;

						}

						for($i = 0; $i < $tableNumber; $i++){

							echo '<tr>';

							for($j = 1; $j < $columnNumber; $j++){

								if(!empty($table[$i][$j - 1])){
									$name = $_SESSION['kanboard'];

									$request = $pdo->prepare("SELECT * FROM $name");
									$request->execute();
									$columnInfo = $request->getColumnMeta($j);
									$columnInfo = $columnInfo['name'];

									$data = explode(" ::", $table[$i][$j - 1]);
									$colorName = $data[2];

									$name = $_SESSION['kanboard']."_colors";
									$request = $pdo->prepare("SELECT * FROM $name WHERE name = '$colorName'");
									$request->execute();
									$colorData = $request->fetchAll();

									echo '<td style="background-color: unset; border: 1px solid white" class="task">';
									echo '<a href="kanboard-dashboard.php?column='.$columnInfo.'&ligne='.$table[$i][$j - 1].'">';
									echo '<div style="width: 100%">';
									echo '<div style="width: 98%">';
									echo '<div style="float: left;">';
									echo '<h3 style="color: white;">'.$data[0].'</h3>';
									echo '</div>';
									echo '<div style="float: right; margin-right: 5px;">';
									echo '<i class="fa-solid fa-circle" style="color: rgb('.$colorData[0][2].','.$colorData[0][3].','.$colorData[0][4].'); margin-top: 22px;"></i>';
									echo '</div>';
									echo '</div>';
									echo '</div>';
									echo '<p style="overflow: hidden; height: 20px; width: 98%; text-overflow: ellipsis;">'.$data[1].'</p>';
									echo '</a>';
									echo '</td>';

								}else{

									echo '<td style="background-color: unset;>';
									echo '<h4 style="color: white"></h4>';
									echo '</td>';

								}

							}

							echo '<tr>';
						}
					?>
				</table>
			</div>
		</section>

		<div id="popup1" class="popup">
			<div class="popup-content">
				<span class="popup-close1" style="margin-left: 2px;">&times;</span>
				<form method="GET" autocomplete="off">
					<br>
					<input type="text" name="column_name" placeholder="Nom de la colonne" class="form-control">
					<br>
					<input type="submit" name="add_column" value="Ajouter" lass="form-control">
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

		<div id="popup2" class="popup">
			<div class="popup-content">
				<span class="popup-close2" style="margin-left: 2px;">&times;</span>

				<form method="GET" autocomplete="off">
					<br>
					<input type="text" name="title" placeholder="Titre">
					<br>
					<textarea style="resize: none;" name="description" placeholder="Description"></textarea>
					<br>
					<select name="color">
						<?php
							$name = $_SESSION['kanboard']."_colors";
							$request = $pdo->prepare("SELECT * FROM $name");
							$request->execute();
							$colorData = $request->fetchAll();
							$colorNumber = $request->rowCount();

							for($i = 0; $i < $colorNumber; $i++) { 
								echo '<option value"'.$colorData[$i][1].'">'.$colorData[$i][1].'</option>';
							}
						?>
					</select>
					<br>
					<input type="submit" name="add_task" value="Ajouter">
					<br>
					<?php 
						if(isset($_GET['error'])){
							if($_GET['error'] == 3) {$error = "Tous les champs ne sont pas completé";}

							echo '<div align="center">';
							echo '<h4 style="color: red;">'.$error.'</h4>';
							echo '</div>';
						}
					?>
				</form>
			</div>
		</div>

		<div id="popup3" class="popup">
			<div class="popup-content">
				<span class="popup-close3" style="margin-left: 2px;">&times;</span>

				<form method="GET" autocomplete="off">
					<?php 
						$data = array();

						if(isset($_GET['column']) && isset($_GET['ligne'])){
							$ligne = $_GET['ligne'];

							$data = explode(' ::', $ligne);
						}
					?>

					<br>
					<input type="text" name="title" placeholder="Titre" value="<?php echo $data[0]; ?>">
					<br>
					<textarea style="resize: none;" name="description" placeholder="Description"><?php echo $data[1]; ?></textarea>
					<br>
					<select name="color">
						<?php
							$name = $_SESSION['kanboard']."_colors";
							$request = $pdo->prepare("SELECT * FROM $name");
							$request->execute();
							$colorData = $request->fetchAll();
							$colorNumber = $request->rowCount();

							for($i = 0; $i < $colorNumber; $i++) { 
								if($data[2] == $colorData[$i][1]){
									echo '<option value"'.$colorData[$i][1].'" selected>'.$colorData[$i][1].'</option>';
								}else{
									echo '<option value"'.$colorData[$i][1].'">'.$colorData[$i][1].'</option>';
								}
							}
						?>
					</select>
					<br>
					<div style="display: inline-flex; width: 100%">
						<input type="submit" name="mod_task" value="Modifier">
						<input type="submit" name="sup_task" value="Supprimer" style="background-color: red;">
					</div>
					<br>
				</form>
			</div>
		</div>

		<div id="popup4" class="popup">
			<div class="popup-content">
				<span class="popup-close4" style="margin-left: 2px;">&times;</span>

				<form method="GET" autocomplete="off">
					<br>
					<select name="user">
						<?php
							$request = $pdo->prepare("SELECT * FROM users");
							$request->execute();
							$userNumber = $request->rowCount();
							$userInfo = $request->fetchAll();

							for($i=0; $i < $userNumber; $i++) { 
								
								if($userInfo[$i][1] != $_SESSION['identifier']){
									$name = $_SESSION['kanboard'];
									$userName = $userInfo[$i][1];

									$request = $pdo->prepare("SELECT * FROM $name WHERE host = '$userName'");
									$request->execute();
									$exist = $request->rowCount();

									if($exist == 0){
										echo '<option value="'.$userName.'">'.$userName.'</option>';
									}

								}

							}
						?>
					</select>
					<br>
					<input type="submit" name="add_partage" value="Ajouter">
					<br><br><br>
					<select name="user">
						<?php
							$request = $pdo->prepare("SELECT * FROM users");
							$request->execute();
							$userNumber = $request->rowCount();
							$userInfo = $request->fetchAll();

							for($i=0; $i < $userNumber; $i++) { 
								
								if($userInfo[$i][1] != $_SESSION['identifier']){
									$name = $_SESSION['kanboard'];
									$userName = $userInfo[$i][1];

									$request = $pdo->prepare("SELECT * FROM $name WHERE host = '$userName'");
									$request->execute();
									$exist = $request->rowCount();

									if($exist == 1){
										echo '<option value="'.$userName.'">'.$userName.'</option>';
									}

								}

							}
						?>
					</select>
					<br>
					<input type="submit" name="delete_partage" value="Retirer" style="background-color: red">
				</form>
			</div>
		</div>

		<div id="popup5" class="popup">
			<div class="popup-content">
				<span class="popup-close5" style="margin-left: 2px;">&times;</span>

				<form method="GET" autocomplete="off">
					<br>
					<table>
						<?php

							$name = $_SESSION['kanboard']."_colors";
							$request = $pdo->prepare("SELECT * FROM $name");
							$request->execute();
							$colorNumber = $request->rowCount();
							$colorData = $request->fetchAll();

							for($i = 0; $i < $colorNumber; $i++){

								if($i % 2 === 0){
									echo '<tr>';
								}

								$color = sprintf("#%02x%02x%02x", $colorData[$i][2], $colorData[$i][3], $colorData[$i][4]);

								
								echo '<td class="color"><input type="color" value="'.$color.'" name="color_'.$i.'"></td>';
								echo '<td class="color" style="padding-right: 10px;"><input type="text" value="'.$colorData[$i][1].'" name="text_'.$i.'"></td>';
								
								if($i % 2 == 1){
									echo '</tr>';
								}

							}
						?>
					</table>
					<br>
					<input type="submit" name="save_color" value="Sauvegarder">
					<br>
					<?php 
						if(isset($_GET['error'])){
							if($_GET['error'] == 4) {$error = "Tous les champs ne sont pas completé";}

							echo '<div align="center">';
							echo '<h4 style="color: red;">'.$error.'</h4>';
							echo '</div>';
						}
					?>
				</form>
			</div>
		</div>
	</body>
</html>

<script>

	var errors = 0;

	var popup1 = document.getElementById("popup1");
	var popup2 = document.getElementById("popup2");
	var popup3 = document.getElementById("popup3");
	var popup4 = document.getElementById("popup4");
	var popup5 = document.getElementById("popup5"); 

	var popupOpen1 = document.getElementById("popup-open1");
	var popupOpen2 = document.getElementById("popup-open2");
	var popupOpen3 = document.getElementById("popup-open3");
	var popupOpen4 = document.getElementById("popup-open4");
	var popupOpen5 = document.getElementById("popup-open5");

	var popupClose1 = document.getElementsByClassName("popup-close1")[0];
	var popupClose2 = document.getElementsByClassName("popup-close2")[0];
	var popupClose3 = document.getElementsByClassName("popup-close3")[0];
	var popupClose4 = document.getElementsByClassName("popup-close4")[0];
	var popupClose5 = document.getElementsByClassName("popup-close5")[0];

	function open1(){ popup1.style.display = "block"; }
	function open2(){ popup2.style.display = "block"; }
	function open3(){ popup3.style.display = "block"; }
	function open4(){ popup4.style.display = "block"; }
	function open5(){ popup5.style.display = "block"; }

	function close1(){ check(); popup1.style.display = "none"; }
	function close2(){ check(); popup2.style.display = "none"; }
	function close3(){ check(); popup3.style.display = "none"; }
	function close4(){ check(); popup4.style.display = "none"; }
	function close5(){ check(); popup5.style.display = "none"; }

	function check(){
		if(errors > 0){
			document.location.href="kanboard-dashboard.php";
		}
	}

	window.onclick = function(event) {
		if (event.target == popup1) { close1(); }
		if (event.target == popup2) { close2(); }
		if (event.target == popup3) { close3(); }
		if (event.target == popup4) { close4(); }
		if (event.target == popup5) { close5(); }
	}

	popupClose1.onclick = function() { close1(); }
	popupClose2.onclick = function() { close2(); }
	popupClose3.onclick = function() { close3(); }
	popupClose4.onclick = function() { close4(); }
	popupClose5.onclick = function() { close5(); }

	function error(r){
		errors = r;

		if(r == 1 || r == 2){
			open1();
		}

		if(r == 3){
			open2();
		}

		if(r == 4){
			open5();
		}
	}

</script>

<?php
	if(isset($_GET['error'])){
		echo '<script>error('.$_GET['error'].');</script>';
	}

	if(isset($_GET['column']) && isset($_GET['ligne'])){

		echo '<script>open3();</script>';
		$_SESSION['column'] = $_GET['column'];
		$_SESSION['ligne'] = $_GET['ligne'];

	}else if(isset($_GET['column'])){

		echo '<script>open2();</script>';
		$_SESSION['column'] = $_GET['column'];

	}


?>

<style>
	table{
		height: 100%;
	}

	.task{
		background-color: white;
		padding-left: 25px;
		width: 10%;
	}

	td h4{
		color: black;
	}

	textarea{
		width: 100%;
		height: 150px;
		padding: 12px 20px;
		margin: 8px 0;
		box-sizing: border-box;
	}

	textarea::placeholder {
		font-family: Georgia, sans-serif;
	}

	select{
		border: none;
		color: white;
		padding: 16px 32px;
		margin: 4px 2px;
		cursor: pointer;
		font-size: 15px;
		font-weight: bold;
		background-color: gray;
		width: 100%;
	}

	option{
		background-color: darkgray;
	}

	span{
		cursor: pointer;
	}

	input[type=color]{
		border: none;
		background-color: inherit;
		height: 40px;
	}
</style>