## LINKS
- https://github.com/pjabadesco/taskqueue-http
- https://www.youtube.com/watch?v=mcX_4EvYka4
- https://github.com/veryacademy/YT_FastAPI_Celery_Redis_Flower_Introduction
- https://github.com/socketio/socket.io-redis-emitter

## COMMANDS
```sh
docker-compose build
docker-compose up

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
        "nvoice_uuid": "DFSF12", 
        "transtype": "new_post"
    },
    "callback_url": "https://example.com/taskqueue/callback"
}
```

## RESPONSE:
```json
{
    "task_id": 123
}
```

## CALLBACK RESPONSE: POST
```json
{
    "status": "success",
    "task_id": 123,
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
            "nvoice_uuid": "DFSF12", 
            "transtype": "new_post"
        },
        "callback_url": "https://example.com/taskqueue/callback"
    }
}
```


## CURL REQUESTS EXAMPLES
```bash
curl --location --request POST 'http://localhost:8000' \
--header 'Content-Type: application/json' \
--data-raw '{
    "name": "new_post",
    "url": "https://yahoo.com",
    "http_method": "POST",
    "headers": {
        "Content-Type": "application/json"
    },
    "body": {
        "epay_invoice_uuid": "DFSF12", 
        "transtype": "new_post"
    },
    "callback_url": "https://yahoo.com"
}'
```
