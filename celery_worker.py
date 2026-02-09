import os
import requests
from requests.exceptions import RequestException
from requests.exceptions import ConnectionError as ReqConnectionError
from requests.exceptions import Timeout as ReqTimeout
import json

from celery import Celery
from dotenv import load_dotenv

load_dotenv(".env")

celery = Celery(__name__)
celery.conf.broker_url = os.environ.get("CELERY_BROKER_URL")
celery.conf.result_backend = os.environ.get("CELERY_RESULT_BACKEND")
celery.conf.task_default_queue = "default"  # v0.9: use "default" instead of "celery"

# v0.9: HTTP request timeout (seconds). Prevents worker hang on slow target URLs.
REQUEST_TIMEOUT = int(os.environ.get("REQUEST_TIMEOUT", 30))

class CallbackTask(celery.Task):
    def on_success(self, retval, task_id, args, kwargs):
        # retval (object) - The return value of the task.
        # task_id (str) - Id of the executed task.
        # args (Tuple) - Original arguments for the task that was executed.
        # kwargs (Dict) - Original keyword arguments for the task that was executed.
        print('{0!r} success: {1!r}'.format(task_id, retval))        
        try:
            headers = args[4]
            channel_id = headers['X-CHANNEL-ID']
        except:
            channel_id = task_id            
        callback_url = args[5]
        if len(callback_url) > 0:
            if callback_url.startswith("http://") or callback_url.startswith("https://"):
                try:
                    requests.post(args[5], data=json.dumps({
                        "status": "success",
                        "task_id": task_id,
                        "channel_id": channel_id,
                        "request": {
                            "taskname": args[0],
                            "url": args[1],
                            "http_method": args[2],
                            "body": args[3],
                            "headers": args[4],
                            "callback_url": args[5]
                        },
                        "response": retval,
                    }), headers={
                        "Content-Type": "application/json",
                    }, timeout=REQUEST_TIMEOUT)
                except RequestException as e:
                    print(e)

    def on_failure(self, exc, task_id, args, kwargs, einfo):
        # exc (Exception) - The exception raised by the task.
        # args (Tuple) - Original arguments for the task that failed.
        # kwargs (Dict) - Original keyword arguments for the task that failed.
        print('{0!r} failed: {1!r}'.format(task_id, exc))
        callback_url = args[5]
        if len(callback_url) > 0:
            if callback_url.startswith("http://") or callback_url.startswith("https://"):
                try:
                    requests.post(args[5], data=json.dumps({
                        "status": "failed",
                        "task_id": task_id,
                        "request": {
                            "taskname": args[0],
                            "url": args[1],
                            "http_method": args[2],
                            "body": args[3],
                            "headers": args[4],
                            "callback_url": args[5]
                        },
                        "einfo": str(einfo),
                    }), headers={
                        "Content-Type": "application/json",
                    }, timeout=REQUEST_TIMEOUT)
                except RequestException as e:
                    print(e)

@celery.task(name="create_task", base=CallbackTask, bind=True,
             max_retries=5)
def create_task(self, taskname, url, http_method, body, headers, callback_url):
    headers.update({'X-TASK-ID': self.request.id})

    # v0.9: Manual retry for network errors (ConnectionError, Timeout).
    # Cannot use autoretry_for due to Celery 5.2.x bug where serialized
    # expires (string) causes TypeError on retry in send_task().
    try:
        if http_method == "GET":
            response = requests.get(url, headers=headers, allow_redirects=True, timeout=REQUEST_TIMEOUT)
        elif http_method == "POST":
            if headers.get('Content-Type') == 'application/json':
                response = requests.post(url, json=body, headers=headers, allow_redirects=True, timeout=REQUEST_TIMEOUT)
            elif headers.get('Content-Type') == 'application/x-www-form-urlencoded':
                response = requests.post(url, data=body, headers=headers, allow_redirects=True, timeout=REQUEST_TIMEOUT)
            else:
                response = requests.post(url, data=json.dumps(body), headers=headers, allow_redirects=True, timeout=REQUEST_TIMEOUT)
        elif http_method == "PUT":
            if headers.get('Content-Type') == 'application/json':
                response = requests.put(url, json=body, headers=headers, allow_redirects=True, timeout=REQUEST_TIMEOUT)
            elif headers.get('Content-Type') == 'application/x-www-form-urlencoded':
                response = requests.put(url, data=body, headers=headers, allow_redirects=True, timeout=REQUEST_TIMEOUT)
            else:
                response = requests.put(url, data=json.dumps(body), headers=headers, allow_redirects=True, timeout=REQUEST_TIMEOUT)
        elif http_method == "PATCH":
            if headers.get('Content-Type') == 'application/json':
                response = requests.patch(url, json=body, headers=headers, allow_redirects=True, timeout=REQUEST_TIMEOUT)
            elif headers.get('Content-Type') == 'application/x-www-form-urlencoded':
                response = requests.patch(url, data=body, headers=headers, allow_redirects=True, timeout=REQUEST_TIMEOUT)
            else:
                response = requests.patch(url, data=json.dumps(body), headers=headers, allow_redirects=True, timeout=REQUEST_TIMEOUT)
        elif http_method == "DELETE":
            response = requests.delete(url, headers=headers, allow_redirects=True, timeout=REQUEST_TIMEOUT)
        else:
            raise RequestException("HTTP Method not supported")
    except (ReqConnectionError, ReqTimeout) as exc:
        raise self.retry(
            exc=exc,
            countdown=min(2 ** self.request.retries * 5, 300),
            max_retries=5,
            expires=None
        )

    # v0.9: Distinguish 4xx (client error) vs 5xx (server error)
    if response.status_code >= 500:
        raise self.retry(
            exc=RequestException(f'{url} returned server error: {response.status_code}'),
            countdown=min(2 ** self.request.retries * 5, 300),
            max_retries=5,
            expires=None
        )
    elif response.status_code >= 400:
        raise RequestException(f'{url} returned client error: {response.status_code}')

    try:
        response_body = response.json()
    except ValueError:
        response_body = response.text

    response_headers = dict(response.headers)
    response_headers.update({'X-TASK-ID': self.request.id})

    return {
        "headers": response_headers,
        "status_code": response.status_code,
        "body": response_body
    }
