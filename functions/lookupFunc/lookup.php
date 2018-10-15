<?php


function get_quote($ticker){
	$json = file_get_contents("https://query1.finance.yahoo.com/v7/finance/options/" . $ticker);
	$response = json_decode($json);
	if($response->optionChain->result[0]->quote->ask > 0){
		return $response->optionChain->result[0]->quote;
	} else if($response->optionChain->result[0]->quote->regularMarketPrice > 0) {
		$response->optionChain->result[0]->quote->bid = $response->optionChain->result[0]->quote->regularMarketPrice;
		$response->optionChain->result[0]->quote->ask = $response->optionChain->result[0]->quote->regularMarketPrice;
		return $response->optionChain->result[0]->quote;
	} else {
		return false;
	}
}

?>