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

function display_ordinal($i)
{
    $l = substr($i,-1);
    $s = substr($i,-2,-1);
     
    return (($l==1&&$s==1)||($l==2&&$s==1)||($l==3&&$s==1)||$l>3||$l==0?'th':($l==3?'rd':($l==2?'nd':'st')));
}

?>