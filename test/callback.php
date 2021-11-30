<?php 
$json = file_get_contents('php://input');

// Converts it into a PHP object
$data = json_decode($json);

error_log(print_r($data, TRUE)); 
syslog(LOG_ERR,'Some message');

?>