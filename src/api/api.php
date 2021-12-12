<?php
try {
    $db_host = "mysql";
    $db_db = "test";
    $db_user = "root";
    $db_pass = "notSecureChangeMe";
    $dbh = new PDO("mysql:host=$db_host;port=3306;dbname=$db_db", $db_user, $db_pass, array( 
        PDO::ATTR_EMULATE_PREPARES => true,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8', 
        PDO::ATTR_PERSISTENT => true
    ));
} catch (PDOException $e) {
    die('DB Connection Error');
};

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

        if($headers['Authorization'] == 'Bearer THISIASASUPERSECRETKEY') {
            $ret = array(
                'status' => 'success',
                'message' => 'Login set successfully. Redirecting to Dashboard...',
            );
        } else {
            $ret = array(
                'status' => 'fail',
                'message' => 'Login failed - invalid token',
            );
        };
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($ret);
        break;
    case 'tq_log':
        $headers = getallheaders();
        $json = file_get_contents('php://input');
        $data = json_decode($json);        

        if($headers['Authorization'] == 'Bearer THISIASASUPERSECRETKEY') {

            $sql = "
                CREATE TABLE IF NOT EXISTS `taskgroups` (
                    `id` bigint(0) NOT NULL AUTO_INCREMENT,
                    `transdate` datetime(0) NOT NULL,
                    `taskgroup` varchar(255) NULL,
                    `channel_id` varchar(255) NULL,
                    `task_id` varchar(255) NULL,
                    `taskname` varchar(255) NULL,
                    `success` tinyint(1) NULL DEFAULT 0,
                    `step` int(11) NULL,
                    `completed` tinyint(1) NULL DEFAULT 0,
                    `data` json NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE INDEX `channel_id`(`taskgroup`, `channel_id`)    
                );
            ";
            $dbh->prepare($sql)->execute();

            $dbh->prepare("
                INSERT INTO taskgroups (transdate, taskgroup, channel_id, task_id, taskname, success, step, completed, data) 
                VALUES (NOW(), :taskgroup, :channel_id, :task_id, :taskname, :success, :step, :completed, :data)
                ON DUPLICATE KEY UPDATE transdate = NOW(), taskgroup = :taskgroup, channel_id = :channel_id, task_id = :task_id, taskname = :taskname, success = :success, step = :step, completed = :completed, data = :data;
            ")->execute(array(
                ':taskgroup' => $data->taskgroup,
                ':channel_id' => $data->channel,
                ':task_id' => $data->task_id,
                ':taskname' => $data->taskname,
                ':success' => $data->success,
                ':step' => $data->step,
                ':completed' => $data->completed,
                ':data' => json_encode($data->data)
            ));

        };

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