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

@celery.task(name="create_task", bind=True, autoretry_for=(RequestException,), retry_backoff=True)
def create_task(self, url, http_method, body, headers):
    # try:
        if http_method == "GET":
            response = requests.get(url, headers=headers)
        elif http_method == "POST":
            response = requests.post(url, data=body, headers=headers)
        elif http_method == "PUT":
            response = requests.put(url, data=body, headers=headers)
        elif http_method == "PATCH":
            response = requests.patch(url, data=body, headers=headers)
        elif http_method == "DELETE":
            response = requests.delete(url, headers=headers)
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
