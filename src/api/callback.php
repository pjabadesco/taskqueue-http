<?php
$redis = new \Redis;
$redis->connect('redis', 6379);

$taskgroup = $_REQUEST['action'];

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
                        $url = 'http://app:8000';
                        $ret = curlPost($url, array(
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
                        $redis->publish($channel_id, json_encode($response));
                    }else{
                        // if login is INVALID return FAILURE
                        $redis->publish($channel_id, json_encode($response));
                    };
                    break;
                case 'test-login01':
                    $redis->publish($channel_id,json_encode($response));
                    break;
            };    
        } else {            
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
    error_log('################# CURLPOST BEGIN #################');        
    error_log(print_r($ret, TRUE));         
    error_log('################# CURLPOST END #################');        
    return $ret;
}

function tq($taskgroup,$message,$data,$completed=1,$step=1,$url_next=''){
    $response = $data->response;
    $request = $data->request;
    $ret = array(
        'message' => $message,
        'transdate' => date('Y-m-d H:i:s'),
        'channel' => $data->channel,
        'task_id' => $data->task_id,
        'taskgroup' => $taskgroup,
        'taskname' => $request->taskname,
        'completed' => $completed,
        'step' => $step,
        'url' => $request->url,
        'url_next' => $url_next,
        'data' => $data
    );
    return $ret;
};
?>