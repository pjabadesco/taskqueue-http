$(document).ready(function() {
    $('#login').focus();

    $('#myForm').on('submit', function(e) {
        e.preventDefault();
        var login = $('#login').val();
        var password = $('#password').val();
        $.ajax({
            url: 'http://localhost:8888',
            type: 'POST',
            dataType: 'json',
            headers: {
                "Content-Type":"application/json"
            },
            data: JSON.stringify({
                "name": "new_post",
                "url": "http://api/api.php?action=login",
                "http_method": "POST",
                "headers": {
                    "Content-Type": "application/json"
                },
                "body": {
                    "login": login,
                    "password": password
                },
                "callback_url": "http://api/api.php?action=login_callback"
            }),
            success: function (data) {
                if (data && data.task_id) {
                    console.log(data.task_id);
                    taskqueue(data.task_id);
                } else {
                    alert('Error');
                };
            }
        });
    });

});


function taskqueue(task_id) {
    var socket = io.connect('http://localhost:3000');  
    var content = $('#content');
    
    socket.on('connect', function() {
        console.log('connected');
        content.html("<b>Connected!</b>");
    });
    
    socket.on(task_id, function (message){
        content.append('<br>'+message);
    }) ;
    
    socket.on('announcement', function (message){
        content.append('<br>'+message);
    }) ;

    socket.on('disconnect', function () {
        console.log('disconnected');
        content.html("<b>Disconnected!</b>");
    });

    socket.connect();
    
    socket.emit('subscribe', {
        channel: task_id
    });

};
