/* jshint browser:true */
/* global require */
require(['jquery', 'routing'],
function($, routing) {
    'use strict';
    $(function() {
        $(document).on('change', 'form select.extend-rel-target-name', function (e) {
            var el     = $(this),
                target = el.find('option:selected').attr('value').replace(/\\/g,'_'),
                query =  routing.generate.apply(routing, ['oro_entityconfig_field_search', {id: target}]),
                fields = $('form select.extend-rel-target-field');

            $(fields).each(function(index, el){
                var is_multiple = typeof $(el).attr('multiple') !== 'undefined' && $(el).attr('multiple') !== false;
                if (is_multiple) {
                    $(el).empty().append('<option value="">Loading...</option>');
                } else {
                    $(el).prev('span').text('loading...');
                }
            });

            $.getJSON(query, function(response) {
                $(fields).each(function(index, el){
                    var items = [];

                    $.each( response, function( key, val ) {
                        items.push("<option value='" + key + "'>" + val + "</option>");
                    });

                    $(el).empty().append(items.join(''));
                    $(el).prev('span').text('Please choice target field...');
                });
            });

            return false;
        });
    });
});
