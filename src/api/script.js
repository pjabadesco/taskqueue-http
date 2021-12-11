$(document).ready(function() {
    $('#login').focus();

    $('#myForm').on('submit', function (e) {
        $('#btn_submit').hide();
        $('#btn_submit_loading').show();
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
                "taskname": "test-login",
                "url": "http://api/api.php?action=login",
                "http_method": "POST",
                "headers": {
                    "Content-Type": "application/json"
                },
                "body": {
                    "login": login,
                    "password": password,
                    "session_id": $('#session_id').val(),
                },
                "callback_url": "http://api/callback.php?action=login"
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
    
    socket.on(task_id, function (message) {
        message = JSON.parse(message);
        content.append('<br>' + message.message);
        switch (message.status) {
            case 'pending':
                break;
            case 'success':
                window.location.href = 'dashboard.php';
                break;
            case 'fail':
                $('#btn_submit').show();
                $('#btn_submit_loading').hide();        
                break;
        };
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
