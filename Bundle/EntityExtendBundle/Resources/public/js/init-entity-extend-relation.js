/* jshint browser:true */
/* global require */
require(['jquery', 'routing'],
function($, routing) {
    'use strict';
    $(function() {
        $(document).on('change', 'form select.extend-rel-target-name', function (e) {
            var el     = $(this),
                target = el.find('option:selected').attr('value'),
                query =  routing.generate.apply(routing, ['oro_entityconfig_field_search', {id: target}]),
                fields = $('form select.extend-rel-target-field');

            $(fields).prev('span').text('loading...');
            fields.empty().append('<option value="">Please choice target field...</option>');

            $.getJSON(query, function(response) {
                var items = [];
                items.push('<option value="">Please choice target field...</option>');
                $.each( response, function( key, val ) {
                    items.push("<option value='" + key + "'>" + val + "</option>");
                });
                fields.empty().append(items.join(''));

                $(fields).prev('span').text('Please choice target field...');
            });

            return false;
        });
    });
});
