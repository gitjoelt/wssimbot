<?php

//debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'pass.php';
define('API_URL', 'https://api.telegram.org/bot'.BOT_TOKEN.'/');
require 'functions/helperFunc/helper.php';
require 'functions/dbFunc/db.php';
require 'functions/telegramFunc/telegram.php';
require 'functions/userFunc/user.php';
require 'functions/ledgerFunc/ledger.php';
require 'functions/calcFunc/calc.php';
require 'functions/lookupFunc/lookup.php';


//Get updates from Telegram
$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!$update) {
  exit;
}

if (isset($update["message"])) {
  processMessage($update["message"]);
}

function processMessage($message) {

  //crawler options
  $crawler_options  = array('http' => array('user_agent' => 'ubuntu:wssimbot'));
  $crawler_context  = stream_context_create($crawler_options);

  // message data
  $message_id = $message['message_id'];
  $chat_id = $message['chat']['id'];
  $tg_id = $message['from']['id'];
  $username = $message['from']['username'];
  $text = $message['text'];

  //COMMAND: wssstart
  if (strpos($text, "/start") === 0) {
    apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "Hello. Type /wsshelp to begin", parse_mode => "HTML"));
  }

  //COMMAND: wsshelp
  if (strpos($text, "/wsshelp") === 0) {
    $html .= "Thank you for playing Wall Street Simulator. Here you can compete with your friends to see who can get the best returns. Please reference the following commands to play.\n\n";
    $html .= "/wssjoin - Type this to participate in the game. You will be granted 10,000 USD to begin trading. You only get one shot at this so don't lose all your money!\n\n";
    $html .= "/wsscashbalance - View your available funds which can be used to buy shares.\n\n";
    $html .= "/wssquote [ticker] - Get a quote on a ticker of your choosing (Quotes may be delayed by 15 minutes).\n\n";
    $html .= "/wssbuy [ticker;quantity] - Purchase shares at anytime through this command. If you are unsure what ticker to type, search for it on finance.yahoo.com -- Whatever the ticker is on there will be the same one you type here.\n\n";
    $html .= "/wsssell [ticker;quantity] - Sell shares at anytime through this command.\n\n";
    $html .= "/wsspositions [username] - Look up your own positions or a friends through their username (leave blank if you want to lookup your own). This will give you a full breakdown of your positions and cash balance, along with your return.\n\n";
    $html .= "/wsshistory - View transactions over the past 90 days\n\n";
    $html .= "/wssrules - Learn about the rules of the game.\n\n";

    $html .= "<em>¤The funds used in this game are made up and do not exist in real life. This game is purely for fun, competition and bragging rights.\n\n¤Quotes are based off of current Bid/Ask, however they may be delayed by 15 minutes.</em>";
    apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $html, parse_mode => "HTML"));
  }

  //COMMAND: wssrules
   if (strpos($text, "/wssrules") === 0) {
    $html .= "1. You cannot short sell or buy options\n";
    $html .= "2. You may only have up to 6 different positions\n";
    $html .= "3. All currencies will be converted to USD to calculate returns\n";
    $html .= "4. You cannot reset your balance\n";
    apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $html, parse_mode => "HTML"));
   }

  //COMMAND: wssjoin
  if (strpos($text, "/wssjoin") === 0) {

    if($username){
      if(!user_exists($tg_id)){
        create_user($tg_id, $username);
        ledger_add_entry($tg_id, '10000');
        apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "Signed up and ready to go. Your account balance is $10,000. Type /wsshelp for more information.", parse_mode => "HTML"));
      } else {
        apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "You are already signed up. Type /wsshelp for more information.", parse_mode => "HTML"));
      }
    } else {
      apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "Looks like you don't have a Telegram username. Please create one first then try again.", parse_mode => "HTML"));
    }

  }

  //COMMAND: wsshistory
  if (strpos($text, "/wsshistory") === 0) {
    apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "↓ View transaction history ↓\nhttps://joeltersigni.com/wssimbot", parse_mode => "HTML"));
  }


  //COMMAND: wsscashbalance
  if (strpos($text, "/wsscashbalance") === 0) {

    if(!user_exists($tg_id)){
      apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "You aren't participating in the game. Type /wssjoin to join.", parse_mode => "HTML"));
      exit;
    }

    $cash = number_format(get_cashbalance($tg_id), 2);
    apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "<b>Available Cash (USD):</b> $" . $cash, parse_mode => "HTML"));
  }

  //COMMAND: wsspositions
  if (strpos($text, "/wsspositions") === 0) {
    $html = ''; $balance = 0;

    //check to see if looking up someone else
    $word = trim_command($text,"/wsspositions");
    $userInput = explode(";", $word);
    if($userInput[0]){
      if($userid = username_to_id($userInput[0])){
        $tg_id = $userid;
        $username = str_replace('@','', $userInput[0]);
        $html .= strtoupper($username . "'s positions\n\n");
      } else {
        apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $userInput[0] . " is not participating in this game.", parse_mode => "HTML"));
        exit;
      }
    }
    /////////////////////////////////

    $positions = get_positions($tg_id);
    if($positions){
      foreach($positions as $ticker => $position){
        if($ticker != "cashbalance" && $position['quantity'] > 0){
          $quote = get_quote($ticker);
          if(!$position['stock_name']) { $position['stock_name'] = $quote->shortName; }
          $html .= "<b>" . $position['stock_name'] . " (" . $ticker . ")</b>\n";
          $html .= "Shares: " . $position['quantity'] . " // Avg. Price: " . number_format($position['price'], 2) . "\n";
          $html .= "<b>Book Value (" . $position['currency'] . "):</b> " . number_format(shares_to_cash($position['quantity'], $position['price']), 2) . "\n";
          $html .= "Market Price: " . $quote->bid . "\n";
          $html .= "<b>Market Value (" . $position['currency'] . "):</b> " . number_format(shares_to_cash($position['quantity'], $quote->bid), 2);
          $html .= " [" . number_format(calc_percentage(shares_to_cash($position['quantity'], $quote->bid), shares_to_cash($position['quantity'], $position['price'])), 2) . "%]";
          $html .= "\n\n";

          //calculate total balance
          $balance = $balance + shares_to_cash($position['quantity'], $quote->bid, $quote->currency);

        }
      }
      $html .= "<b>Cash:</b> $" . number_format($positions['cashbalance'], 2) . "\n";
      $balance = $balance + $positions['cashbalance'];
      $html .= "<b>Balance:</b> $" . number_format($balance, 2) . "\n";
      $html .= "<b>Return:</b> " . number_format(calc_capitalgain($balance, 10000), 2) . " [" .number_format(calc_percentage($balance, 10000),2) . "%]\n";
      $html .= "<em>¤ Cash, Balance, and Return calculated in USD</em>";
      apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $html, parse_mode => "HTML"));
    } else {
      apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $username . " has no positions.", parse_mode => "HTML"));
    }
  }

  //COMMAND: wssbuy
  if (strpos($text, "/wssbuy") === 0) {

    //Check Valid user
    if(!user_exists($tg_id)){
      apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "You aren't participating in the game. Type /wssjoin to join.", parse_mode => "HTML"));
      exit;
    }
    //Check no more than 6 positions
    if(no_of_positions($tg_id) >= 6){
      apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "Buy order failed. You cannot own more than 6 positions at one time.", parse_mode => "HTML"));
      exit;
    }

    $word = trim_command($text,"/wssbuy");
    $userInput = explode(";", $word);
    $ticker = $userInput[0]; $quantity = abs($userInput[1]);

    //Check Valid request
    if(count($userInput) != 2){
      apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "You improperly formatted your buy request. It should be /wssbuy ticker;quantity. (ex. /wssbuy amd;50)", parse_mode => "HTML"));
      exit;
    }

    if($quote = get_quote($ticker)){
      $price = $quote->ask; $currencyCode = $quote->currency;
      $cashbalance = get_cashbalance($tg_id);
      $cost = shares_to_cash($quantity, $price, $currencyCode);
      $remainder = can_afford($cashbalance, $cost);
      if($remainder >= 0){
        ledger_add_entry($tg_id, $cost, $quote->longName, $quote->symbol, $quantity, $price, $currencyCode, "buy");
        $html = "Bought " . $quantity . " shares of " . $quote->symbol . " @ (" . $currencyCode . ") " . $price . "\n<b>Total Cost (USD):</b> " . number_format($cost, 2); 
        apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $html, parse_mode => "HTML"));
      } else {
        $html = "You don't have enough available funds to make this purchase.\n";
        $html .= "<b>Funds Required (USD):</b> " . $cost . "\n<b>Your Balance (USD):</b> " . number_format($cashbalance, 2);
        apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $html, parse_mode => "HTML"));
      }
    } else {
      apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "Unable to retireve data on this ticker. Check that you typed the ticker correct (see if it works on Yahoo Finance too). Try again during market hours if it is still not working.", parse_mode => "HTML"));
    }

  }

  //COMMAND: wsssell
  if (strpos($text, "/wsssell") === 0) {

    if(!user_exists($tg_id)){
      apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "You aren't participating in the game. Type /wssjoin to join.", parse_mode => "HTML"));
      exit;
    }

    $word = trim_command($text,"/wsssell");
    $userInput = explode(";", $word);
    $ticker = $userInput[0]; $quantity = abs($userInput[1]);

    if(count($userInput) != 2){
      apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "You improperly formatted your sell request. It should be /wsssell ticker;quantity. (ex. /wsssell amd;50)", parse_mode => "HTML"));
      exit;
    }

    $shares = get_shares($tg_id, $ticker);
    if($shares >= $quantity){
      if($quote = get_quote($ticker)){
        $price = $quote->bid; $currencyCode = $quote->currency;
        $proceeds = shares_to_cash($quantity, $price, $currencyCode);
        $quantity = 0 - $quantity;
        ledger_add_entry($tg_id, $proceeds, $quote->longName, $quote->symbol, $quantity, $price, $currencyCode, "sell");
        $html = "Sold " . abs($quantity) . " shares of " . $quote->symbol . " @ (" . $currencyCode . ") " . $price . "\n<b>Total Proceeds (USD):</b> " . number_format($proceeds, 2); 
        apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $html, parse_mode => "HTML"));
      }
    } else {
      $html = "You cannot sell more shares than you own.";
      apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $html, parse_mode => "HTML"));
    }

  }


  //COMMAND: wssquote
  if (strpos($text, "/wssquote") === 0) {
    $ticker = trim_command($text,"/wssquote");

    if($quote = get_quote($ticker)){
      $percentageChange = number_format(calc_percentage($quote->regularMarketPrice, $quote->regularMarketPreviousClose), 2);
      $html .= "<b>" . $quote->longName . " (" . $quote->symbol . ")</b>\n";
      if($quote->regularMarketChange > 0){
        $html .= "▲ " . $quote->regularMarketPrice . " (" . number_format($quote->regularMarketChange, 2) . ") [" . $percentageChange . "%]\n";
      } else {
        $html .= "▼ " . $quote->regularMarketPrice . " (" . number_format($quote->regularMarketChange, 2) . ") [" . $percentageChange . "%]\n";
      }
      $html .= "<b>Bid:</b> " . $quote->bid . " // <b>Ask:</b> " . $quote->ask . "\n";
      $html .= "<b>Market:</b> " . $quote->fullExchangeName . " - " . get_marketstate($quote->marketState) . "\n";
      $html .= "<em>¤ Currency in " . $quote->currency . "</em>\n\n";

      $html .= "/////////// TECHNICALS ///////////\n";
      $html .= "<b>Day High:</b> " . $quote->regularMarketDayHigh . " // <b>Day Low:</b> " . $quote->regularMarketDayLow . "\n";
      $html .= "<b>52W High:</b> " . $quote->fiftyTwoWeekHigh . " // <b>52W Low:</b> " . $quote->fiftyTwoWeekLow . "\n";
      $html .= "<b>Volume:</b> " . number_format($quote->regularMarketVolume) . "\n";
      $html .= "<b>Market Cap:</b> " . number_format($quote->marketCap) . "\n";

      apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $html, parse_mode => "HTML"));

    } else {
      apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "Unable to retireve data on this ticker. Check that you typed the ticker correct (see if it works on Yahoo Finance too).", parse_mode => "HTML"));
    }
  }

////////////////End process message
}


