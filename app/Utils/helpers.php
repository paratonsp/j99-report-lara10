<?php
  
function dateFormat($date,$format){
    return \Carbon\Carbon::createFromFormat('Y-m-d', $date)->format($format);    
}

function trimString($string, $repl, $limit) 
{
  if(strlen($string) > $limit) 
  {
    return substr($string, 0, $limit) . $repl; 
  }
  else 
  {
    return $string;
  }
}

function sluggify($string)
{
   return strtolower(str_replace(" ","-",$string));
}

function generateUuid()
{
   return Illuminate\Support\Str::uuid();
}