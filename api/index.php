<?php

include '../pass.php';
require '../functions/helperFunc/helper.php';
require '../functions/dbFunc/db.php';
require '../functions/userFunc/user.php';
require '../functions/ledgerFunc/ledger.php';
require '../functions/calcFunc/calc.php';
require '../functions/lookupFunc/lookup.php';

header('Content-type:application/json;charset=utf-8');

$tg_id = $_GET['tg_id'];
$rdate = $_GET['rdate'];

echo json_encode(get_ledger());

?>