(function () {
    'use strict';

    var sendSetIdentity = function(e) {
        var form = e.target;
        var formData = new FormData(form);
        var xhr = new XMLHttpRequest();

        xhr.addEventListener('load', function (e) {
            window.top.location.reload();
        });

        xhr.addEventListener('error', function (e) {
            form.yiiActiveForm('updateMessages', data.responseJSON, true);
        });

        xhr.open(form.method, form.action);
        xhr.send(formData);
    };

    var form = document.getElementById('debug-userswitch__set-identity');

    form.addEventListener('submit', function (e) {
        sendSetIdentity(e);
        e.preventDefault();
    });

    document.getElementById('debug-userswitch__reset-identity').addEventListener('submit', function (e) {
        sendSetIdentity(e);
        e.preventDefault();
    });

    var filter = document.getElementById('debug-userswitch__filter');

    filter.addEventListener('click', function (e) {
        if (e.target.nodeName === 'TD' && e.target.parentElement.parentElement.nodeName === 'TBODY') {
            document.querySelector('#debug-userswitch__set-identity #user_id').value = filter.dataset.key;
            form.submit();
        }
    });
})();