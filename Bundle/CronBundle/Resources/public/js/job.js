$(function() {
    var statusDaemon = $('#status-daemon'),
        img = statusDaemon.closest('div').find('img');

    $('#run-daemon, #stop-daemon').click(function (e) {
        var el = $(this);

        img.show();

        $.getJSON(el.attr('href'), function (data) {
            if (data.error) {
                alert(data.message);
            } else {
                el
                  .closest('div').find('span:first').toggleClass('label-success label-important').text($.isNumeric(data.message) ? 'Running' : 'Not running').end()
                  .closest('div').find('span:last').text(data.message).end();

                switchButtons(!$.isNumeric(data.message));
            }

            img.hide();
        });

        return false;
    });

    setInterval(function () {
        img.show();

        $.get(statusDaemon.attr('href'), function (data) {
            data = parseInt(data);

            statusDaemon
              .closest('div').find('span:first').removeClass(data > 0 ? 'label-important' : 'label-success').addClass(data > 0 ? 'label-success' : 'label-important').text(data > 0 ? 'Running' : 'Not running').end()
              .closest('div').find('span:last').text(data > 0 ? data : 'N/A').end();

            switchButtons(!data);

            img.hide();
        });
    }, 30000);

    function switchButtons(run) {
        if (run) {
            $('#run-daemon').show();
            $('#stop-daemon').hide();
        } else {
            $('#run-daemon').hide();
            $('#stop-daemon').show();
        }
    }
})