/* jshint browser:true, devel:true */
/* global require */
require(['jquery', 'oro/translator'],
function($, __) {
    'use strict';
    $(function() {
        $(document).on('click', '#run-daemon, #stop-daemon', function (e) {
            var el = $(this),
                img = $('#status-daemon').closest('div').find('img');

            img.show();

            $.getJSON(el.attr('href'), function (data) {
                if (data.error) {
                    alert(data.message);
                } else {
                    el.closest('div')
                            .find('span:first')
                                .toggleClass('label-success label-important')
                                .text($.isNumeric(data.message) ? __('Running') : __('Not running'))
                            .end()
                        .closest('div').find('span:last').text(data.message).end();

                    switchButtons(!$.isNumeric(data.message));
                }

                img.hide();
            });

            return false;
        });

        $(document).on('click', '.stack-trace a', function (e) {
            var el = $(this),
                traceCon = el.closest('.stack-trace').find('.traces'),
                traceConVis = traceCon.is(':visible');

            if (el.next('.trace').length) {
                el.next('.trace').toggle();
            } else {
                $('.traces').hide();
                traceCon.toggle(!traceConVis);
            }

            el.find('img').toggleClass('hide');

            return false;
        });

        setInterval(function () {
            var statusBtn = $('#status-daemon'),
                img = statusBtn.closest('div').find('img');
            if (!statusBtn.length) {
                return;
            }

            img.show();

            $.get(statusBtn.attr('href'), function (data) {
                data = parseInt(data, 10);

                statusBtn
                    .closest('div')
                        .find('span:first')
                            .removeClass(data > 0 ? 'label-important' : 'label-success')
                            .addClass(data > 0 ? 'label-success' : 'label-important')
                            .text(data > 0 ? __('Running') : __('Not running'))
                        .end()
                    .closest('div').find('span:last').text(data > 0 ? data : __('N/A')).end();

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
    });
});