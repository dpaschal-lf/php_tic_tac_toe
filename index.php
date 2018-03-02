<?php
session_start();

$playerSymbols = ['X','O'];

function debug($message){
	if(!empty($_GET['debug'])){
		print($message);
	}
}

function createGameBoard($width, $height){
	$array = [];
	for($x = 0; $x<$width; $x++){
		$array[$x] = [];
		for($y=0; $y<$height; $y++){
			$array[$x][$y] = '';
		}
	}
	return $array;
}

function createPlayerDisplay($symbol, $active=false){
	$additionalClass = $active ? 'active' : '';
	$html = "<div class='playerDisplay $additionalClass'>$symbol</div>";
	return $html;
}

function createAllPlayers($symbol_array, $currentPlayer){
	forEach($symbol_array as $key=>$value){
		print(createPlayerDisplay($value, $key===$currentPlayer));
	}
}

function checkForWin($col, $row){
	$col = (int)$col;
	$row = (int)$row;
	$vectors = [
		[['x'=>0, 'y'=>1], ['x'=>0, 'y'=>-1]],//updown
		[['x'=>-1, 'y'=>0], ['x'=>1, 'y'=>0]],//leftright
		[['x'=>-1, 'y'=>-1], ['x'=>1, 'y'=>1]],//upleft/downright
		[['x'=>-1 ,'y'=>1], ['x'=>1, 'y'=>-1]]//upright/downleft
	];
	$currentSymbol = $_SESSION['gameBoard'][$row][$col];
	forEach($vectors as $oppositeVectors){
		$checkDir0Count = checkForWinInDirection(['x'=>$col, 'y'=>$row], $oppositeVectors[0], $currentSymbol);
		$checkDir1Count = checkForWinInDirection(['x'=>$col, 'y'=>$row], $oppositeVectors[1], $currentSymbol);
		$count = 1 + $checkDir0Count + $checkDir1Count;
		debug("***dir 1 count:$checkDir0Count dir 2 count:$checkDir1Count***");
		if($count===3){
			return true;
		}
	}
	return false;
}

function checkForWinInDirection($startingPoint, $vector, $checkingAgainst){
	$nextLocation = [];
	debug("<br>starting location {$startingPoint['y']}:{$startingPoint['x']} <br>");
	$nextLocation['x'] = $startingPoint['x']+ $vector['x'];
	$nextLocation['y'] = $startingPoint['y']+ $vector['y'];
	@debug("<br>checking {$nextLocation['y']}:{$nextLocation['x']}.");
	if(array_key_exists($nextLocation['y'],$_SESSION['gameBoard']) && array_key_exists($nextLocation['x'], $_SESSION['gameBoard'][$nextLocation['y']])){
		debug("<br>It is ***{$_SESSION['gameBoard'][$nextLocation['y']][$nextLocation['x']]}*** versus $checkingAgainst");
		if($_SESSION['gameBoard'][$nextLocation['y']][$nextLocation['x']] !== $checkingAgainst){
			debug("<div>not the same</div>");
			return 0; //not the same
		} else {
			debug("<br>it is the same, checking again");	
			return 1 + checkForWinInDirection($nextLocation, $vector, $checkingAgainst);
		}
	} else{
		debug("<div>out of bounds</div>");
		return 0; //out of bounds
	}
}

if(empty($_SESSION['gameBoard']) || !empty($_GET['restart'])){
	$_SESSION['gameBoard'] = createGameBoard(3,3);
	$_SESSION['currentPlayer'] = 0;
}


if(isset($_GET['row']) && isset($_GET['col'])){
	$currentPlayer = intval($_SESSION['currentPlayer']);

	$y=$_GET['row'];
	$x= $_GET['col'];

	$_SESSION['gameBoard'][$y][$x]= $playerSymbols[ $currentPlayer ];
	$result = checkForWin($x, $y);
	if($result){	
		print($playerSymbols[ $currentPlayer ] . ' WINS');
		?><a href="index.php?restart=true">Play again?</a>
		<?php
		exit();
	}
	$currentPlayer = 1 - $currentPlayer;
	$_SESSION['currentPlayer'] = $currentPlayer;
}

if(!isset($currentPlayer)){
	$currentPlayer=0;
}

?>
<!DOCTYPE html>
<html>
<head>
	<title></title>
	<style type="text/css">
		*{
			box-sizing: border-box;
		}
		body{
			margin: 0;
			height: 100vh;
			width: 100vw;
		}
		#playerInfo{
			height: 10%;
			background-color: lavender;
		}
		.playerDisplay{
			float: left;
			border: 1px solid black;
			width: 50%;
		}
		#gameArea{
			height: 90%;
		}
		.cell{
			width: 33%;
			height: 33%;
			border: 1px solid black;
			float: left;
		}
		.cell a{
			height: 100%;
			width: 100%;
			background-color: lightgreen;
			display: inline-block;
		}
		.active{
			background-color: yellow;
		}

	</style>
</head>
<body>
<header id="playerInfo">
	<?php 
	createAllPlayers($playerSymbols, $currentPlayer);
	?>
</header>
<main id="gameArea">
	<?php
		forEach($_SESSION['gameBoard'] as $y_key=>$row){
			forEach($row as $x_key => $cell ){
				$link = $cell!='' ? 'index.php' : "index.php?row=$y_key&col=$x_key";
				?><div class="cell"><a href="<?=$link;?>"><?=$cell;?></a></div><?php
			}
		}
	?>
</main>
</body>
</html>