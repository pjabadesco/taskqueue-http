from fastapi import Body, FastAPI
from fastapi.responses import JSONResponse
from fastapi.middleware.cors import CORSMiddleware
import uvicorn
import json
from pprint import pprint

from celery_worker import create_task


app = FastAPI()
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

@app.get("/")
def index():
    return JSONResponse({"message": "Hello World"})

@app.post("/")
def index_post(data=Body(...)):
    http_method = data.get("http_method", "GET")
    url = data.get("url", "")
    body = data.get("body", {})
    headers = data.get("headers", '{"Content-Type": "application/json"}')
    expires = data.get("expires", 86400)
    taskname = data.get("taskname", "celery_worker_queue")
    callback_url = data.get("callback_url", "")

    if len(url) == 0:
        return JSONResponse({"message": "URL is required"}, status_code=500)
    if not url.startswith("http://") and not url.startswith("https://"):
        return JSONResponse({"message": "URL must start with http:// or https://"}, status_code=500)

    if len(callback_url) > 0:
        if not callback_url.startswith("http://") and not callback_url.startswith("https://"):
            return JSONResponse({"message": "callback URL must start with http:// or https://"}, status_code=500)

    if http_method not in ["GET", "POST", "PUT", "PATCH", "DELETE"]:
        return JSONResponse({"message": "HTTP Method must be GET, POST, PUT, PATCH, or DELETE"}, status_code=500)

    if len(body) > 0:
        try:
            json.dumps(body)
        except:
            return JSONResponse({"message": "Body must be in JSON format"}, status_code=500)

    if len(headers) > 0:
        try:
            json.dumps(headers)
        except:
            return JSONResponse({"message": "Headers must be in JSON format"}, status_code=500)

    try:
        expires = int(expires)
    except:
        return JSONResponse({"message": "Dispatch deadline must be an integer"}, status_code=500)
    if expires < 0:
        return JSONResponse({"message": "Dispatch deadline must be a positive integer"}, status_code=500)

    try:
        # create task
        task = create_task.apply_async(
            shadow=taskname,
            args=(taskname, url, http_method, body, headers, callback_url),
            # max_retries=5,
            # expires=expires
        )
        # task = create_task.delay(url, http_method, body, headers)
    except Exception as e:
        return JSONResponse({"message": str(e)}, status_code=500)

    return JSONResponse(
        {"message": "Task created successfully", "task_id": task.id}
    )

    # task = create_task.delay(http_method, url, body, headers, expires)
    # # return JSONResponse({"Result": task.get()})
    # return JSONResponse({"Result": task.ready()})

# if __name__ == "__main__":
#     uvicorn.run(app)
