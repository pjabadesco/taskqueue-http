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
                    })
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
                    })
                except RequestException as e:
                    print(e)

@celery.task(name="create_task", base=CallbackTask, bind=True, autoretry_for=(RequestException,), retry_backoff=True)
def create_task(self, taskname, url, http_method, body, headers, callback_url):
    # try:
        headers.update({'X-TASK-ID': self.request.id})          

        if http_method == "GET":
            response = requests.get(url, headers=headers, allow_redirects=True)
        elif http_method == "POST":
            if headers.get('Content-Type') == 'application/json':            
                response = requests.post(url, json=body, headers=headers, allow_redirects=True)
            elif headers.get('Content-Type') == 'application/x-www-form-urlencoded':
                # response = requests.post(url, params=body, headers=headers, allow_redirects=True)
                response = requests.post(url, data=body, headers=headers, allow_redirects=True)
            else:
                response = requests.post(url, data=json.dumps(body), headers=headers, allow_redirects=True)
        elif http_method == "PUT":
            if headers.get('Content-Type') == 'application/json':            
                response = requests.put(url, json=body, headers=headers, allow_redirects=True)
            elif headers.get('Content-Type') == 'application/x-www-form-urlencoded':
                # response = requests.put(url, params=body, headers=headers, allow_redirects=True)
                response = requests.put(url, data=body, headers=headers, allow_redirects=True)
            else:
                response = requests.put(url, data=json.dumps(body), headers=headers, allow_redirects=True)
        elif http_method == "PATCH":
            if headers.get('Content-Type') == 'application/json':            
                response = requests.patch(url, json=body, headers=headers, allow_redirects=True)
            elif headers.get('Content-Type') == 'application/x-www-form-urlencoded':
                # response = requests.patch(url, params=body, headers=headers, allow_redirects=True)
                response = requests.patch(url, data=body, headers=headers, allow_redirects=True)
            else:
                response = requests.patch(url, data=json.dumps(body), headers=headers, allow_redirects=True)
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

        try:
            response_body = response.json()
        except ValueError:
            response_body = response.text

        response_headers = dict(response.headers)
        # response_headers = response_headers + ({'X-TASK-ID': self.request.id},)
        response_headers.update({'X-TASK-ID': self.request.id})   

        return {
            # "url": url, 
            # "http_method": http_method, 
            "headers": response_headers,
            # "headers": headers,
            # "body": body, 
            "status_code": response.status_code, 
            # "response_headers": response_headers, 
            "body": response_body
        }
        # return response.json()
        # return str(response)
        
    # except Exception as e:
    #     self.retry(countdown=5, exc=e)
        # raise Exception(str(e))
