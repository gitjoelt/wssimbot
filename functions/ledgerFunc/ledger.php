<?php

function ledger_add_entry($tg_id, $cash_change = '', $stock_name = '', $stock_ticker = '', $quantity = '', $price = '', $currency = 'USD', $action = ''){
	$conn = db_connect();
	$sql = "INSERT INTO ledger (tg_id, cash_change, stock_name, stock_ticker, quantity, price, currency, action) VALUES ('" . $tg_id . "','" . $cash_change . "','" . $stock_name . "','" . $stock_ticker . "','" . $quantity . "','" . $price . "','" . $currency . "','" . $action . "')";
	$conn->query($sql);
}

?>