<?php

function record_error($message){
  $myfile = fopen("errorlog.txt", "a");
  fwrite($myfile, $message);
  fclose($myfile);
}

function trim_command($text, $command, $encode = false, $spaces = false, $shoogz = false)
{
  
  $word = str_replace($command, '', $text);
  if(!$shoogz){
    $word = str_replace("@wssimbot", '', $word);
  }
  $word = trim($word);
  if($encode){ $word = urlencode($word); }
  if($spaces){ $word = str_replace(' ', '_', $word); }
  return $word;
}

?>