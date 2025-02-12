<?php

function dateFormat($date, $format)
{
  return \Carbon\Carbon::createFromFormat('Y-m-d', $date)->format($format);
}

function trimString($string, $repl, $limit)
{
  if (strlen($string) > $limit) {
    return substr($string, 0, $limit) . $repl;
  } else {
    return $string;
  }
}

function sluggify($string)
{
  return strtolower(str_replace(" ", "-", $string));
}

function generateUuid()
{
  return Illuminate\Support\Str::uuid();
}

function generateColor($index)
{
  $color = [
    "#00FFFF",
    "#0000FF",
    "#00008B",
    "#ADD8E6",
    "#800080",
    "#FFFF00",
    "#00FF00",
    "#FF00FF",
    "#FFC0CB",
    "#C0C0C0",
    "#808080",
    "#FFA500",
    "#A52A2A",
    "#800000",
    "#008000",
    "#808000",
    "#7FFFD4"
  ];

  return $color[$index];
}
