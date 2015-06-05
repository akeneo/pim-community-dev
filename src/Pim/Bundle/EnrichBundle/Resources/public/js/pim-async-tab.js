define(
    ['jquery', 'oro/loading-mask'],
    function ($, LoadingMask) {
        'use strict';

        return function (tab) {
            var $tab = $(tab);
            var target = $tab.attr('href');
            if (!target || target === '#' || target.indexOf('javascript') === 0) {
                return;
            }
            var $target = $(target);

            if (!$target.attr('data-loaded') && !$target.attr('data-loading') && $target.attr('data-url')) {
                $target.attr('data-loading', 1);
                if (!$target.hasClass('active')) {
                    $target.addClass('active');
                }
                var loadingMask = new LoadingMask();
                loadingMask.render().$el.appendTo($target)
                    .css({ 'position': 'absolute', 'width': '100%', 'height': '80%' });
                loadingMask.show();

                $.get($target.attr('data-url'), function (data) {
                    $target.html(data).attr('data-loaded', 1).removeAttr('data-loading');
                    loadingMask.hide().$el.remove();
                    $target.closest('form').trigger('tab.loaded', $target);
                });
            }
        };
    }
);

