export DOCKER_DEFAULT_PLATFORM=linux/amd64  
docker-compose build


## APP
docker tag taskqueue-http_app:latest pjabadesco/taskqueue-http-app:0.8
docker push pjabadesco/taskqueue-http-app:0.8

docker tag pjabadesco/taskqueue-http-app:0.8 pjabadesco/taskqueue-http-app:latest
docker push pjabadesco/taskqueue-http-app:latest

docker tag pjabadesco/taskqueue-http-app:latest ghcr.io/pjabadesco/taskqueue-http-app:latest
docker push ghcr.io/pjabadesco/taskqueue-http-app:latest

## FLOWER
docker tag taskqueue-http_flower:latest pjabadesco/taskqueue-http-flower:0.8
docker push pjabadesco/taskqueue-http-flower:0.8

docker tag pjabadesco/taskqueue-http-flower:0.8 pjabadesco/taskqueue-http-flower:latest
docker push pjabadesco/taskqueue-http-flower:latest

docker tag pjabadesco/taskqueue-http-flower:latest ghcr.io/pjabadesco/taskqueue-http-flower:latest
docker push ghcr.io/pjabadesco/taskqueue-http-flower:latest

## CELERY WORKER
docker tag taskqueue-http_celery_worker_01:latest pjabadesco/taskqueue-http-celery-worker:0.8
docker push pjabadesco/taskqueue-http-celery-worker:0.8

docker tag pjabadesco/taskqueue-http-celery-worker:0.8 pjabadesco/taskqueue-http-celery-worker:latest
docker push pjabadesco/taskqueue-http-celery-worker:latest

docker tag pjabadesco/taskqueue-http-celery-worker:latest ghcr.io/pjabadesco/taskqueue-http-celery-worker:latest
docker push ghcr.io/pjabadesco/taskqueue-http-celery-worker:latest
