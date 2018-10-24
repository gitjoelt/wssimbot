<?php

function truncate_leaderboard(){
	$conn = db_connect();
	$sql = "TRUNCATE TABLE leaderboards";
	$conn->query($sql);
}

function set_leaderboard($tg_id, $username, $return_cash, $return_percentage) {
	$conn = db_connect();
	$sql = "INSERT INTO leaderboards (tg_id, username, return_cash, return_percentage) VALUES ('" . $tg_id . "','" . $username . "','" . $return_cash . "','" . $return_percentage . "')";
	$conn->query($sql);
}

function get_position_by_user($tg_id){
	$conn = db_connect();
	$sql = "SELECT id FROM leaderboards WHERE tg_id = '" . $tg_id . "'";
	$result = $conn->query($sql);
	if($result->num_rows){ 
		$row = $result->fetch_row();
		return $row[0];
	} else { return false; }
}

function get_leaderboards(){
	$conn = db_connect();
	$sql = "SELECT * FROM leaderboards";
	$result = $conn->query($sql);
	
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()) {
			$leaderboards[] = array("tg_id" => $row['tg_id'],
									"id" => $row['id'],
						     		"username" => $row['username'],
						     		"return_cash" => $row['return_cash'],
						     		"return_percentage" => $row['return_percentage'],
						     		"updated_date" => $row['updated_date']);
		}
		return $leaderboards;
	}

	return false;
}

?>