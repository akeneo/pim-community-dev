<?php

$msg = getenv('msg');
$date = getenv('currentDate');

json_decode($msg, true);

if (json_last_error() === JSON_ERROR_SYNTAX and !empty($msg)) {
  // If json document is malformed, log it as raw text with the following infos
  $message = array(
    'channel' => "queue-daemon-wrapper",
    'message' => $msg,
    'datetime' => array(
      'date' => $date, 'timezone' => 'Etc/UTC', 'timezone_type' => '3'),
    'level' => '250', 'level_name' => 'INFO'
      );
  echo json_encode($message) . PHP_EOL;
  } elseif (json_last_error() === JSON_ERROR_NONE) {
    // If it is a valid JSON document log it as it is
    echo $msg . PHP_EOL;
  }

