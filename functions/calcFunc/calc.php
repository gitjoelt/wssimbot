<?php

function convert_currency($amount, $fromCurrencyCode, $toCurrencyCode = "USD"){
	$query =  "{$fromCurrencyCode}_{$toCurrencyCode}";
	$json = file_get_contents("https://free.currencyconverterapi.com/api/v6/convert?q=" . $query . "&compact=ultra");
	$response = json_decode($json);
	$val = $response->{"$query"};
	$total = $val * $amount;
	return $total;
}

function shares_to_cash($quantity, $pps, $currency = 'USD'){
	if($currency != "USD"){
		$amount = $quantity * $pps;
		return convert_currency($amount, $currency, 'USD');
	}
	return $quantity * $pps;
}

function calc_costavg($prevpps, $prevquantity, $pps, $quantity){
	if($prevquantity > 0){
		$costavg = ($prevpps * $prevquantity) + ($quantity * $pps);
		$totalshares = $prevquantity + $quantity;
		$costavg = $costavg / $totalshares;
		return $costavg;
	} 

	return $pps;
}

//returns >= 0 if there is enough cash
function can_afford($cash, $cost){
	return $cash - $cost;
}

function calc_capitalgain($marketVal, $bookVal){
	return $marketVal - $bookVal;
}

function calc_percentage($marketVal, $bookVal)
{
	$calc = ($marketVal - $bookVal) / $bookVal;
	return $calc * 100;
}

?>