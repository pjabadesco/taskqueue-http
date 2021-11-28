https://www.youtube.com/watch?v=mcX_4EvYka4
https://github.com/veryacademy/YT_FastAPI_Celery_Redis_Flower_Introduction

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

curl -X POST http://localhost:8000/ -H "Content-Type: application/json" --data '{"name":"test", "url": "https://bahay.ph", "http_method": "GET"}'

curl -X POST http://localhost:8000/ -H "Content-Type: application/json" --data '{"name":"test", "url": "https://bahay.ph", "http_method": "GET"}'

curl -X POST http://localhost:8000/ -H "Content-Type: application/json" --data '{"name":"test", "url": "https://api.neuracash.com/transactions", "http_method": "GET", "body": {"epay_invoice_uuid": "DFSF12", "transtype": "img_kih_newbusiness"}}'


SELECT * FROM PAYMENTS -  1000000000000ms
SELECT * FROM PAYMENTS WHERE ID=1 -  1ms
SMALL TRANSACTION IS HEALTHIER

TASKGROUP - INSERT ALL STEPS
    REQUEST 1-1000

TASKGROUP - INSERT STEP 1 BY 1

    REQUEST01
        url: "https://api.imgcorp.net/img_kih_newbusiness/epayph/invoices/ipn/insert"
        taskgroup: "img_kih_newbusiness"
        on_success_endpoint: "https://api.imgcorp.net/img_kih_newbusiness/img/payments/payments_master/insert"
        data: "{
            transdate: "2021-12-12"
        }"
    ON_FAILURE:
        call function to update TRANSGROUP TABLE OF CURRENT STEP,ENDPOINT,DATA,COMPLETED
    ON_SUCCESS:
        call function to update TRANSGROUP TABLE OF CURRENT STEP,ENDPOINT,DATA,COMPLETED        
        RESPONSE
            taskgroup: "img_kih_newbusiness"
            on_success_endpoint: "https://api.imgcorp.net/img_kih_newbusiness/img/payments/payments_master/insert"
            request: "{
                transdate: "2021-12-12"
            }"
            response: "{
                transid: 123
                transdate: "2021-12-12"
            }"

    REQUEST02
        url: "https://api.imgcorp.net/img_kih_newbusiness/img/payments/payments_master/insert"
        taskgroup: "img_kih_newbusiness"
        transdate: "2021-12-12"
        on_success_endpoint: "https://api.imgcorp.net/img_kih_newbusiness/img/payments/payments_details/insert"
        data: "{}"
    RESPONSE02
        url: "https://api.imgcorp.net/img_kih_newbusiness/img/payments/payments_master/insert"
        taskgroup: "img_kih_newbusiness"
        transdate: "2021-12-12"
        on_success_endpoint: "https://api.imgcorp.net/img_kih_newbusiness/img/payments/payments_details/insert"
        request_data: "{}"
        response_data: "{transid: 123}"




RESPONSE:  CELERY_ID, response_date:{"transid": 131231, "transdate":"2021-01-01"}

REQUEST02:  STEP02 - data {transid: 131231}



curl "NEW PAYMENT EPAYPH_UUID: JDFKJD" body: {trantype: img_kih_newbusiness} POST