define(
    ['jquery', 'underscore', 'jquery.select2'],
    function ($, _) {
        'use strict';

        return function () {
            $('form input.multiselect').each(function () {
                var $el   = $(this),
                    value = _.map(_.compact($el.val().split(',')), $.trim),
                    tags  = _.map(_.compact($el.attr('data-tags').split(',')), $.trim);
                tags = _.union(tags, value).sort();
                $el.select2({ tags: tags, tokenSeparators: [',', ' '] });
            });

            $('select').each(function () {
                var $el    = $(this),
                    $empty = $el.children('[value=""]');
                if ($empty.length && $empty.html()) {
                    $el.attr('data-placeholder', $empty.html());
                    $empty.html('');
                }
            });

            $('form select[data-placeholder]').select2({ allowClear: true });
            $('form select:not(.select2-offscreen)').select2();
        };
    }
);
