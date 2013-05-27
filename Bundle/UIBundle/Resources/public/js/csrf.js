$(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-Header': 1
        }
    });
})