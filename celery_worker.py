import os
import time
import requests
from requests.exceptions import RequestException
import json

from celery import Celery
from dotenv import load_dotenv

load_dotenv(".env")

celery = Celery(__name__)
celery.conf.broker_url = os.environ.get("CELERY_BROKER_URL")
celery.conf.result_backend = os.environ.get("CELERY_RESULT_BACKEND")

class BaseWebhookTask(celery.Task):
    def on_success(self, retval, task_id, args, kwargs):
        # retval (object) - The return value of the task.
        # task_id (str) - Id of the executed task.
        # args (Tuple) - Original arguments for the task that was executed.
        # kwargs (Dict) - Original keyword arguments for the task that was executed.
        print('{0!r} success: {1!r}'.format(task_id, retval))
        webhook_url = args[4]
        if len(webhook_url) > 0:
            if webhook_url.startswith("http://") or webhook_url.startswith("https://"):
                try:
                    requests.post(args[4], data={
                        "status": "success",
                        "task_id": task_id,
                        "args": json.dumps(args),
                        "retval": json.dumps(retval),
                    })
                except RequestException as e:
                    print(e)

    def on_failure(self, exc, task_id, args, kwargs, einfo):
        # exc (Exception) - The exception raised by the task.
        # args (Tuple) - Original arguments for the task that failed.
        # kwargs (Dict) - Original keyword arguments for the task that failed.
        print('{0!r} failed: {1!r}'.format(task_id, exc))
        webhook_url = args[4]
        if len(webhook_url) > 0:
            if webhook_url.startswith("http://") or webhook_url.startswith("https://"):
                try:
                    requests.post(args[4], data={
                        "status": "success",
                        "task_id": task_id,
                        "args": json.dumps(args),
                        "einfo": str(einfo),
                    })
                except RequestException as e:
                    print(e)

@celery.task(name="create_task", base=BaseWebhookTask, bind=True, autoretry_for=(RequestException,), retry_backoff=True)
def create_task(self, url, http_method, body, headers, webhook_url):
    # try:
        if http_method == "GET":
            response = requests.get(url, headers=headers, allow_redirects=True)
        elif http_method == "POST":
            response = requests.post(url, data=body, headers=headers, allow_redirects=True)
        elif http_method == "PUT":
            response = requests.put(url, data=body, headers=headers, allow_redirects=True)
        elif http_method == "PATCH":
            response = requests.patch(url, data=body, headers=headers, allow_redirects=True)
        elif http_method == "DELETE":
            response = requests.delete(url, headers=headers, allow_redirects=True)
        else:
            raise RequestException("HTTP Method not supported")

        # GET response http code
        # response_headers = {}
        # for key, value in response.headers.items():
        #     response_headers[key] = value

        if not response.ok:
            raise RequestException(f'{url} returned unexpected response code: {response.status_code}')

        return {
            "url": url, 
            "http_method": http_method, 
            "headers": headers, 
            "body": body, 
            "response_code": response.status_code, 
            # "response_headers": response_headers, 
            "response_body": response.text
        }
        # return response.json()
        # return str(response)
        
    # except Exception as e:
    #     self.retry(countdown=5, exc=e)
        # raise Exception(str(e))
