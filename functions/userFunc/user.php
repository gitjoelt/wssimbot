<?php

function user_exists($tg_id) {
	$conn = db_connect();
	$sql = "SELECT * FROM users WHERE tg_id = '" . $tg_id . "'";
	$result = $conn->query($sql);
	if($result->num_rows > 0){
		return true;
	}
	return false;
}

function username_to_id($username){
	$username = str_replace('@','',$username);
	$conn = db_connect();
	$sql = "SELECT tg_id FROM users WHERE username = '" . $username . "'";
	$result = $conn->query($sql);
	if($result->num_rows){ 
		$row = $result->fetch_row();
		return $row[0];
	} else { return false; }
}

function create_user($tg_id, $username) {
	$conn = db_connect();
	$sql = "INSERT INTO users (tg_id, username) VALUES ('" . $tg_id . "','" . $username . "')";
	$conn->query($sql);
}

function get_users(){
	
	$conn = db_connect();
	$sql = "SELECT * FROM users";
	$result = $conn->query($sql);
	
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()) {
			$users[] = array("tg_id" => $row['tg_id'],
						     "username" => $row['username']);
		}
		return $users;
	}

	return false;
}

function get_cashbalance($tg_id){
	$conn = db_connect();
	$sql = "SELECT * FROM ledger WHERE tg_id = '" . $tg_id . "'";
	$result = $conn->query($sql);
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()) {
			if($row['action'] == "buy"){
				$cash_change = 0 - $row['cash_change'];
			} else {
				$cash_change = $row['cash_change'];
			}
	    	$amount = $amount + $cash_change;
		}
		return $amount;
	}

	return false;
}

function get_positions($tg_id){
	$conn = db_connect();
	$sql = "SELECT * FROM ledger WHERE tg_id = '" . $tg_id . "'";
	$result = $conn->query($sql);
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()) {

			if($row['stock_ticker']){
				//cost avg price
				if($positions[$row['stock_ticker']]['price']){
					$costavg = calc_costavg($positions[$row['stock_ticker']]['price'], $positions[$row['stock_ticker']]['quantity'], $row['price'], $row['quantity']);
				} else {
					$costavg = $row['price'];
				}
	    		$positions[$row['stock_ticker']] = array("stock_name" => $row['stock_name'],
		    											"quantity" => $positions[$row['stock_ticker']]['quantity'] + $row['quantity'],
		    											"price" => $costavg,
		    											"currency" => $row['currency']);
	    	}

	    	if($row['action'] == "buy"){
				$cash_change = 0 - $row['cash_change'];
			} else {
				$cash_change = $row['cash_change'];
			}
	    	$positions['cashbalance'] = $cash_change + $positions['cashbalance'];
		}
		ksort($positions);
		return $positions;
	}

	return false;
}

function no_of_positions($tg_id){
	$conn = db_connect();
	$sql = "SELECT * FROM ledger WHERE tg_id = '" . $tg_id . "'";
	$result = $conn->query($sql);
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()) {
			$positions[$row['stock_ticker']] += $row['quantity'];
			if($positions[$row['stock_ticker']] == 0){
				unset($positions[$row['stock_ticker']]);
			}
		}
		return count($positions);
	} 

	return false;

}

function get_most_traded($tg_id){
	$conn = db_connect();
	$sql = "SELECT * FROM ledger WHERE tg_id = '" . $tg_id . "'";
	$result = $conn->query($sql);
	if($result->num_rows > 0){

		while($row = $result->fetch_assoc()) {
			$positions[$row['stock_ticker']] += 1;
		}
		arsort($positions);
		$top = array_slice($positions,0,1);
		return key($top);
	} 

	return false;

}

function get_shares($tg_id, $ticker){
	$conn = db_connect();
	$sql = "SELECT * FROM ledger WHERE tg_id = '" . $tg_id . "' AND stock_ticker ='" . $ticker . "'";
	$result = $conn->query($sql);
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()) {
			$shares = $shares + $row['quantity'];
		}

		return $shares;
	} 

	return false;
}

?>
