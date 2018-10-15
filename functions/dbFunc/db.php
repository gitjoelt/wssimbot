<?php

function db_connect(){
	return new mysqli('localhost', DB_USER, DB_PASS, DB_NAME);
}

?>