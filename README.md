## TECHNOLOGY
- Redis
- Celery
- Celery Worker
- Flower
- MySQL
- PHP
- TaskGroups

## LINKS
- [API Page] (http://localhost/)
- [Flower] (http://localhost:5556/)
- [Redis Insight] (http://localhost:8001/)
- [WebSocket Server] (http://localhost:3000)
- [TaskQueue Page] (http://localhost:8888)
- [PhpMyAdmin] (http://localhost:8082)

## INSTALLATION
```sh
docker-compose build
docker-compose up
```

## REQUEST: POST
```json
{
    "taskname": "test-login",
    "url": "http://api/api.php?action=login",
    "http_method": "POST",
    "headers": {
        "Content-Type": "application/json"
    },
    "body": {
        "login": "admin",
        "password": "admin"
    },
    "callback_url": "http://api/callback.php?action=login"
}
```

## RESPONSE:
```json
{
    "message": "Task created successfully",
    "task_id": "06b650d4-8d21-4b20-ac8b-be62fc656997"
}
```

## CALLBACK RESPONSE: POST
```json
{
    "status":"success",
    "task_id":"b0c84268-f687-499d-998d-584985da9df0",
    "request":{
        "taskname":"test-login",
        "url":"http://api/api.php?action=login",
        "http_method":"POST",
        "body":{
            "login":"admin",
            "password":"admin",
            "session_id":"5aee3a7be4b4abc9a063b799cf8b8244"
        },
        "headers":{
            "Content-Type":"application/json",
            "X-TASK-ID":"b0c84268-f687-499d-998d-584985da9df0"
        },
        "callback_url":"http://api/callback.php?action=login"
    },
    "response":{
        "headers":{
            "Content-Type":"application/json",
            "X-TASK-ID":"b0c84268-f687-499d-998d-584985da9df0"
        },
        "status_code":200,
        "body":{
            "status":"success",
            "message":"Your credentials are valid. Please wait while we setup your login session.",
            "session_id":"5aee3a7be4b4abc9a063b799cf8b8244"
        }
    }
}
```

## TEST: CURL REQUESTS
```bash
curl --location --request POST 'http://localhost:8888' \
--header 'Content-Type: application/json' \
--data-raw '{
    "taskname":"test-login",
    "url":"http://api/api.php?action=login",
    "http_method":"POST",
    "body":{
        "login":"admin",
        "password":"admin",
        "session_id":"5aee3a7be4b4abc9a063b799cf8b8244"
    },
    "headers":{
        "Content-Type":"application/json"
    },
    "callback_url":"http://api/callback.php?action=login"
}'
```

## REFERENCES
- https://github.com/pjabadesco/taskqueue-http
- https://www.youtube.com/watch?v=mcX_4EvYka4
- https://github.com/veryacademy/YT_FastAPI_Celery_Redis_Flower_Introduction
