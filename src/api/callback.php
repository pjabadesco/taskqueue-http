<?php
$json = file_get_contents('php://input');
$data = json_decode($json);
$response = $data->response->body;
$request = $data->request->body;
$channel_id = ($data->channel_id)?$data->channel_id:$data->task_id;        

if(strlen($channel_id)==0){
    header("HTTP/1.1 500 Internal Server Error");
    header('Content-Type: application/json; charset=utf-8');
    die(json_encode(array(
        'status' => 'error',
        'message' => 'not allowed'
    )));    
};

$redis = new \Redis;
$redis->connect('redis', 6379);

$tq_url = 'http://app:8000';
$tq_ch = curl_init();
curl_setopt($tq_ch, CURLOPT_URL,$tq_url);

$taskgroup = $_REQUEST['action'];
switch($taskgroup) {
    case 'login':
        $json = file_get_contents('php://input');
        $data = json_decode($json);
        $response = $data->response->body;
        $request = $data->request->body;
        $channel_id = ($data->channel_id)?$data->channel_id:$data->task_id;        
        
        // $redis->publish($channel_id,json_encode($data));  
        error_log('################# LOGIN BEGIN #################');        
        error_log(print_r($data, TRUE));         
        error_log('################# LOGIN END #################');        
    
        if($data->status == 'success') {
            $tq_success = 1;
            switch($data->request->taskname){
                case 'test-login':
                    $tq_step = 1;
                    if($response->status=='success'){
                        // if login is VALID redirect to SET SESSION at /test-login
                        $tq_completed = 0;
                        $ret = tq_post(array(
                            "taskname" => "test-login01",
                            "url" => "http://api/api.php?action=login01",
                            "http_method" => "POST",
                            "headers" => array(
                                "Content-Type" => "application/json",
                                "X-CHANNEL-ID" => $channel_id,
                                "Authorization" => "Bearer THISIASASUPERSECRETKEY"
                            ),
                            "body" => $response,
                            "callback_url" => "http://api/callback.php?action=login"
                        ));
                        $response->status = 'pending';
                    }else{
                        // if login is INVALID return FAILURE
                        $tq_completed = 1;
                    };
                    break;
                case 'test-login01':
                    $tq_step = 2;
                    $tq_completed = 1;
                    break;
            };    
            $redis->publish($channel_id, json_encode($response));
        } else {       
            $tq_success = 0;     
            $tq_step = 0;
            $tq_completed = 0;
            // LOG FAILED
            // INSERT ALL FAILURES TO MYSQL DB
        };
        break;
    default:
        $data = array(
            "hello" => "world",
            "sdsdfsdf" => "xcvx"
        );
        $redis->publish('announcement', 'This is an announcement.');
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);    
        die();
}

tq_log($taskgroup,$data,$tq_success,$tq_step,$tq_completed);
curl_close($tq_ch);

function tq_post($data) {
    global $tq_ch;
    curl_setopt($tq_ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json'
    ));
    curl_setopt($tq_ch, CURLOPT_POST, 1);
    curl_setopt($tq_ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($tq_ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($tq_ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($tq_ch, CURLOPT_TCP_FASTOPEN, 1);
    $ret = curl_exec($tq_ch);
    // curl_close ($tq_ch);
    $ret = json_decode($ret, true);  
    error_log('################# TQ_POST BEGIN #################');        
    error_log(print_r($ret, TRUE));         
    error_log('################# TQ_POST END #################');        
    return $ret;
}

function tq_log($taskgroup,$data,$success,$step=1,$completed=1){
    global $tq_url;
    $response = $data->response->body;
    $request = $data->request;
    tq_post(array(
        "taskname" => 'tq_log',
        "url" => 'http://api/api.php?action=tq_log',
        "http_method" => "POST",
        "headers" => array(
            "Content-Type" => "application/json",
            "Authorization" => "Bearer THISIASASUPERSECRETKEY"
        ),
        "body" => array(
            'taskgroup' => $taskgroup,
            'channel' => $data->channel_id,
            'task_id' => $data->task_id,
            'taskname' => $request->taskname,
            'success' => $success,
            'step' => $step,
            'completed' => $completed,
            'data' => $data
        )
    ));
    return;
};
?>