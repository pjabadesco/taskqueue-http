## TECHNOLOGY
- Redis
- Celery
- Celery Worker
- Flower

## LINKS
- [API Page] (http://localhost/)
- [Flower] (http://localhost:5556/)
- [Redis Insight] (http://localhost:8001/)
- [WebSocket Server] (http://localhost:3000)
- [TaskQueue Page] (http://localhost:8888)

## INSTALLATION
```sh
docker-compose build
docker-compose up
```

## REQUEST: POST
```json
{
    "name": "new_post",
    "url": "https://example.com/post/insert",
    "http_method": "POST",
    "headers": {
        "Content-Type: application/json"
    },
    "body": {
        "invoice_uuid": "TEST123", 
        "transtype": "new_post"
    },
    "callback_url": "https://example.com/taskqueue/callback"
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
    "status": "success",
    "task_id": "06b650d4-8d21-4b20-ac8b-be62fc656997",
    "retval": {
        "transid": 232,
        "transdate": "2021-12-12"
    },
    "args": {
        "name": "new_post",
        "url": "https://example.com/post/insert",
        "http_method": "POST",
        "headers": {
            "Content-Type: application/json"
        },
        "body": {
            "invoice_uuid": "TEST123", 
            "transtype": "new_post"
        },
        "callback_url": "https://example.com/taskqueue/callback"
    }
}
```

## TEST: CURL REQUESTS
```bash
curl --location --request POST 'http://localhost:8888' \
--header 'Content-Type: application/json' \
--data-raw '{
    "name": "login",
    "url": "http://192.168.100.130/api.php?action=login",
    "http_method": "POST",
    "headers": {
        "Content-Type": "application/json"
    },
    "body": {
        "login": "TEST123", 
        "password": "new_post"
    },
    "callback_url": "http://192.168.100.130/api.php?action=login_callback"
}'
```

## REFERENCES
- https://github.com/pjabadesco/taskqueue-http
- https://www.youtube.com/watch?v=mcX_4EvYka4
- https://github.com/veryacademy/YT_FastAPI_Celery_Redis_Flower_Introduction
