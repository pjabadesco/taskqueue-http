## RUN 
docker run -it --rm -p 80:80 -v ~/Documents/Sites/GITHUB/pjabadesco/taskqueue-http/test:/var/www/html/ pjabadesco/php74-apache-mssql-mysql:1.1

## LINKS
https://www.youtube.com/watch?v=NwHq1-FkQpU

## TASKQUEUE FLOW
1. CLIENT: login
2. API: post to taskqueue to get TASKID
3. CLIENT: Creates Socket.IO using TASKID as socketid.. "please wait"
4. TQ: task worker callback when success/fail
5. API: receives callback posts to REDIS
6. REDIS: updates and informs SOCKET.IO of status
7. SOCKET.IO: update socketid TASKID
8. CLIENT: redirects to protected page

