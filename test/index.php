<?php 
sleep(3);
$data = array(
    "hello" => "world",
    "sdsdfsdf" => "xcvx"
);
header('Content-Type: application/json; charset=utf-8');
echo json_encode($data);
?>