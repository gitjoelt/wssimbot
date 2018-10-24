<?php

include '../pass.php';
require '../functions/helperFunc/helper.php';
require '../functions/dbFunc/db.php';
require '../functions/userFunc/user.php';
require '../functions/userFunc/leaderboards.php';
require '../functions/ledgerFunc/ledger.php';
require '../functions/calcFunc/calc.php';
require '../functions/lookupFunc/lookup.php';

header('Access-Control-Allow-Origin: *');  
header('Content-type:application/json;charset=utf-8');

$tg_id = $_GET['tg_id'];
$position = $_GET['position'];
$mostTraded = $_GET['mosttraded'];
$leaderboard = $_GET['leaderboard'];

if($tg_id && $position) {
	$position = get_position_by_user($tg_id);
	$position = $position . display_ordinal($position);
	$position = array("position" => $position);
	echo json_encode($position);
} else if ($tg_id && $mostTraded) {
	$mostTraded = array("mostTraded" => get_most_traded($tg_id));
	echo json_encode($mostTraded);
} else if ($leaderboard) {
	echo json_encode(get_leaderboards());
} else {
	echo json_encode(get_ledger());
}




?>