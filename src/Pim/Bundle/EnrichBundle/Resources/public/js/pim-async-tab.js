define(
    ['jquery', 'oro/loading-mask'],
    function ($, LoadingMask) {
        'use strict';

        return function (tab) {
            var $tab = $(tab);
            var target = $tab.attr('href');
            // TODO can you use something else than !trucmuch ? As null === trucmuch ?
            if (!target || '#' === target || 0 === target.indexOf('javascript')) {
                return;
            }
            var $target = $(target);

            // TODO same here if (undefined === $target.attr('data-loaded') etc)
            if (!$target.attr('data-loaded') && !$target.attr('data-loading') && $target.attr('data-url')) {
                $target.attr('data-loading', 1);
                if (!$target.hasClass('active')) {
                    $target.addClass('active');
                }
                var loadingMask = new LoadingMask();
                loadingMask.render().$el.appendTo($target).css({ 'position': 'absolute', 'width': '100%', 'height': '80%' });
                loadingMask.show();

                $.get($target.attr('data-url'), function(data) {
                    $target.html(data).attr('data-loaded', 1).removeAttr('data-loading');
                    loadingMask.hide().$el.remove();
                    $target.closest('form').trigger('tab.loaded', $target);
                });
            }
        };
    }
);

