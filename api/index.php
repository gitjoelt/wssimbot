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
$leaderboard = $_GET['leaderboard'];

if($tg_id) {

	//ranking
	$position = get_position_by_user($tg_id);
	$position = $position . display_ordinal($position);

	//most traded
	$mosttraded = get_most_traded($tg_id);

	//join date
	$joindate = get_joindate($tg_id);
	$joindate = date('M d, Y', strtotime($joindate));

	//total trades
	$totaltrades = get_total_trades($tg_id);

	$userinfo = array("position" => $position, "mostTraded" => $mosttraded, "joinDate" => $joindate, "totalTrades" => $totaltrades);
	echo json_encode($userinfo); 

} else if ($leaderboard) {
	echo json_encode(get_leaderboards());
} else {
	echo json_encode(get_ledger());
}




?>