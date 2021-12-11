<?php
switch($_REQUEST['action']) {
    case 'login':
        // DO LOGIN
        sleep(2);
        try {
            sleep(1);
            $headers = getallheaders();
            $task_id = trim(@$headers['X-TASK-ID']);

            $json = file_get_contents('php://input');
            $data = json_decode($json);        

            if($data->login == 'admin' && $data->password == 'admin') {
                // SET SESSION
                session_id($data->session_id);
                session_start();
                $_SESSION['login'] = $data->login;

                $ret = array(
                    'status' => 'success',
                    'message' => 'Your credentials are valid. Please wait while we setup your login session.',
                    'session_id' => $data->session_id
                );
            } else {
                $ret = array(
                    'status' => 'fail',
                    'message' => 'Login failed',
                    'session_id' => $data->session_id
                );
            };

            error_log('################# LOGIN BEGIN #################');        
            error_log(print_r($data, TRUE));         
            error_log('################# LOGIN END #################');        
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($ret);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        break;
    case 'login01':
        sleep(2);
        $headers = getallheaders();
        $json = file_get_contents('php://input');
        $data = json_decode($json);        

        $ret = array(
            'status' => 'success',
            'message' => 'Login set successfully. Redirecting to Dashboard...',
        );
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($ret);
        break;
    default:
        $data = array(
            "hello" => "world",
            "sdsdfsdf" => "xcvx"
        );
        // $redis->publish('announcement', 'This is an announcement.');
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);    
}

?>