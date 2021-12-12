<?php
$redis = new \Redis;
$redis->connect('redis', 6379);

$tq_url = 'http://app:8000';
$taskgroup = $_REQUEST['action'];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$tq_url);

switch($taskgroup) {
    case 'login':
        $json = file_get_contents('php://input');
        $data = json_decode($json);
        $response = $data->response->body;
        $request = $data->request->body;
        $channel_id = ($data->channel_id)?$data->channel_id:$data->task_id;        
        
        // $redis->publish($channel_id,json_encode($data));  
        // error_log('################# LOGIN BEGIN #################');        
        // error_log(print_r($data, TRUE));         
        // error_log('################# LOGIN END #################');        
    
        if($data->status == 'success') {
            switch($data->request->taskname){
                case 'test-login':
                    if($response->status=='success'){
                        // if login is VALID redirect to SET SESSION at /test-login
                        $ret = tqPost(array(
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
                        tq_log($taskgroup,$data,1,1,0);
                    }else{
                        // if login is INVALID return FAILURE
                        tq_log($taskgroup,$data,1,1,1);
                    };
                    break;
                case 'test-login01':
                    tq_log($taskgroup,$data,1,1,1);
                    break;
            };    
            $redis->publish($channel_id, json_encode($response));
        } else {            
            tq_log($taskgroup,$data,0,0,0);
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
}

function tqPost($data) {
    global $ch;
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json'
    ));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($ch, CURLOPT_TCP_FASTOPEN, 1);
    $ret = curl_exec($ch);
    curl_close ($ch);
    $ret = json_decode($ret, true);  
    error_log('################# tqPOST BEGIN #################');        
    error_log(print_r($ret, TRUE));         
    error_log('################# tqPOST END #################');        
    return $ret;
}

function tq_log($taskgroup,$data,$success,$step=1,$completed=1){
    global $tq_url;
    $response = $data->response->body;
    $request = $data->request;

    tqPost(array(
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

    return $ret;
};
?>