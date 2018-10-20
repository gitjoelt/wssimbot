<?php

function ledger_add_entry($tg_id, $cash_change = '', $stock_name = '', $stock_ticker = '', $quantity = '', $price = '', $currency = 'USD', $action = '')
{
	$conn = db_connect();
	$sql = "INSERT INTO ledger (tg_id, cash_change, stock_name, stock_ticker, quantity, price, currency, action) VALUES ('" . $tg_id . "','" . $cash_change . "','" . $stock_name . "','" . $stock_ticker . "','" . $quantity . "','" . $price . "','" . $currency . "','" . $action . "')";
	$conn->query($sql);
}

function get_ledger()
{
	$rdate = date("Y-m-d H:i:s", strtotime("-90 days"));
	$conn = db_connect();
	$sql = "SELECT ledger.tg_id, ledger.cash_change, ledger.stock_name, ledger.stock_ticker, ledger.quantity, ledger.price, ledger.currency, ledger.action, ledger.action_date, users.username FROM ledger LEFT JOIN users ON ledger.tg_id = users.tg_id WHERE ledger.action_date >= '" . $rdate . "' ORDER BY action_date DESC";
	
	$result = $conn->query($sql);
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()) {

			$action_date = date('M d Y G:i:s (T)', strtotime($row['action_date']));
			if(!$row['action']) { 
				$action = "join"; 
				$quantity = ""; 
			} else { 
				$action = $row['action'];
				$quantity = abs($row['quantity']);
			}

			$ledger[] = array("tg_id" => $row['tg_id'],
							  "username" => $row['username'],
							  "cash_change" => number_format($row['cash_change'], 2),
							  "stock_name" => $row['stock_name'],
							  "stock_ticker" => $row['stock_ticker'],
							  "quantity" => $quantity,
							  "price" => $row['price'],
							  "currency" => $row['currency'],
							  "action" => $action,
							  "action_date" => $action_date);
		}

		return $ledger;
	}

	return false;
}

?>