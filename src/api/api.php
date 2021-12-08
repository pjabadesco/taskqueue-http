<?php
require_once 'vendor/autoload.php';

switch($_REQUEST['action']) {
    case 'login':
        $json = file_get_contents('php://input');
        // Converts it into a PHP object
        $data = json_decode($json);        

        $redis = new \Redis(); // Using the Redis extension provided client
        $redis->connect('redis', '6379');
        $emitter = new SocketIO\Emitter($redis);
        // $emitter->emit('pubsub', array('task_id' => $data->task_id, 'message' => 'very object'));
        $emitter->emit('pubsub', 'test');
        $emitter->broadcast->emit('pubsub', 'such data');
        $emitter->emit('announcement', ['action' => 'remove']);

        echo '{"status": "ok"}';    
        // $username = $_REQUEST['username'];
        // $password = $_REQUEST['password'];
        // if($username == 'admin' && $password == 'admin') {
        //     $_SESSION['username'] = $username;
        //     echo 'success';
        // } else {
        //     echo 'fail';
        // }
        break;
    case 'callback':
        $json = file_get_contents('php://input');
        // Converts it into a PHP object
        $data = json_decode($json);        
        error_log(print_r($data, TRUE));         
        break;
    default:
        sleep(3);
        $data = array(
            "hello" => "world",
            "sdsdfsdf" => "xcvx"
        );
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);    
}
?>