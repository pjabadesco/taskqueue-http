<?php
$redis = new \Redis;
$redis->connect('redis', 6379);

switch($_REQUEST['action']) {
    case 'login':
        try {
            $headers = getallheaders();
            $task_id = trim($headers['X-TASK-ID']);

            $json = file_get_contents('php://input');
            $data = json_decode($json);        

            $message = 'Intializing task: '.$task_id;
            sleep(1);
            $redis->publish($task_id, $message);

            if($data->username == 'admin' && $data->password == 'admin') {
                $ret = array(
                    'status' => 'success', 
                    'message' => 'Login successful',
                    'transdate' => date('Y-m-d H:i:s'),
                    'channel' => $task_id,
                    'task_id' => $task_id,
                    'taskgroup' => 'taskgroup',
                    'taskname' => 'taskname',
                    'completed' => '0',
                    'step' => '1',
                    'url' => 'http://localhost/api/api.php?action=step1&taskid='.$task_id,
                    'url_next' => 'http://localhost/api/api.php?action=step2&taskid='.$task_id,
                    'data' => ''            
                );
                $message = 'Task: '.$task_id.' completed successfully';
                $redis->publish($task_id, $message);
                $redis->publish('task-completed', $task_id);
            } else {
                $message = 'Task: '.$task_id.' failed';
                $redis->publish($task_id, $message);
                $redis->publish('task-failed', $task_id);
            }

            $ret = array(
                'status' => 'ok',
                'message' => $message,
            );
            error_log('################# LOGIN BEGIN #################');        
            error_log(print_r($data, TRUE));         
            error_log('################# LOGIN END #################');        
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($ret);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        break;
    case 'login_01':
        $url = 'http://app:8888';
        $data = array(
            "name" => "test",
            "url" => "http://api/api.php?action=login_01",
            "http_method" => "GET",
            "headers" => array(
                "Content-Type" => "application/json"
            ),
            "body" => array(
                "agentcode" => "demo", 
                "pass" => "testing2020"
            ),
            "callback_url" => "https://api.img-corp.net/taskqueue/login"
        
        );
        $ret = curlPost($url, $data);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($ret);
        break;
    case 'callback':
        $json = file_get_contents('php://input');
        $data = json_decode($json);
        $task_id = trim($data->task_id);

        $message = 'Status: '.$data->status.' Task: '.$task_id.' completed';
        usleep(250000);
        $redis->publish($task_id, $message);

        error_log('################# CALLBACK BEGIN #################');        
        error_log(print_r($data, TRUE));         
        error_log('################# CALLBACK END #################');        
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($ret);

        break;
    default:
        $data = array(
            "hello" => "world",
            "sdsdfsdf" => "xcvx"
        );
        $redis->publish('announcement', 'This is an announcement.');
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);    
}

function curlPost($url, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json'
    ));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $ret = curl_exec($ch);
    curl_close ($ch);
    $ret = json_decode($ret, true);        
    return $ret;
}
?>