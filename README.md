## TECHNOLOGY
- Redis
- Celery
- Celery Worker
- Flower

## LINKS
[API Page] (http://localhost/)
[Flower] (http://localhost:5556/)
[Redis Insight] (http://localhost:8001/)
[WebSocket Server] (http://localhost:3000)
[TaskQueue Page] (http://localhost:8888)

- https://github.com/pjabadesco/taskqueue-http
- https://www.youtube.com/watch?v=mcX_4EvYka4
- https://github.com/veryacademy/YT_FastAPI_Celery_Redis_Flower_Introduction
- https://github.com/socketio/socket.io-redis-emitter

## COMMANDS
```sh
conda env list
conda create --name taskqueue-http python=3.9
conda activate taskqueue-http
conda env remove --name taskqueue-http
pip install -r requirements.txt

pip3 install fastapi
pip3 install uvicorn
pip3 install orjson
pip3 freeze > requirements.txt
python3 ./app.py
uvicorn main:app --host 0.0.0.0 --port 8001

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
