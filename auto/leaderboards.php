<?php

include '../pass.php';
require '../functions/helperFunc/helper.php';
require '../functions/dbFunc/db.php';
require '../functions/userFunc/user.php';
require '../functions/ledgerFunc/ledger.php';
require '../functions/calcFunc/calc.php';
require '../functions/lookupFunc/lookup.php';
require '../functions/userFunc/leaderboards.php';

if($users = get_users())
{
	foreach($users as $user){
		$balance = 0; //reset
		$positions = get_positions($user['tg_id']);
	    if($positions){
	      foreach($positions as $ticker => $position){
	        if($ticker != "cashbalance" && $position['quantity'] > 0){
	          $quote = get_quote($ticker);
	          //calculate total balance
	          $balance = $balance + shares_to_cash($position['quantity'], $quote->bid, $quote->currency);

	        }
	      }

	      $balance = $balance + $positions['cashbalance'];
	      $return_cash = $balance - 10000;
	      $return_percentage = calc_percentage($balance, 10000);

	      $leaderboards[] = array("tg_id" => $user['tg_id'],
	      						  "username" => $user['username'],
	      						  "return_cash" => $return_cash,
	      						  "return_percentage" => $return_percentage);

	    }
	}

	usort($leaderboards, function($a, $b) {
    	return $b['return_cash'] > $a['return_cash'];
	});

	if(count($leaderboards) > 0){
		truncate_leaderboard(); //erase table data
		foreach($leaderboards as $entry){
			set_leaderboard($entry['tg_id'], $entry['username'], number_format($entry['return_cash'], 2), number_format($entry['return_percentage'], 2));
		}
	}
}

?>

<pre><?php print_r($leaderboards) ?></pre>
