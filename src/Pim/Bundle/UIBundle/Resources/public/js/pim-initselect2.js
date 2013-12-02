define(
    ['jquery', 'underscore', 'jquery.select2'],
    function ($, _) {
        'use strict';

        return function (id) {
            var $el = $('#' + id),
                options = {};

            if ($el.hasClass('multiselect')) {
                var value = _.map(_.compact($el.val().split(',')), $.trim),
                    tags  = _.map(_.compact($el.attr('data-tags').split(',')), $.trim);
                    tags = _.union(tags, value).sort();
                options = { tags: tags, tokenSeparators: [',', ' '] };
            } else {
                if ($el.attr('data-placeholder') && $el.attr('data-placeholder').length) {
                    options = { allowClear: true };
                } else {
                    var $empty = $el.children('[value=""]');
                    if ($empty.length && $empty.html()) {
                        $el.attr('data-placeholder', $empty.html());
                        $empty.html('');
                        options = { allowClear: true };
                    }
                }
            }

            $el.select2(options);
        };
    }
);
