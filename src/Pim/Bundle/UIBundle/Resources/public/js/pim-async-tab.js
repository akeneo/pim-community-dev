define(
    ['jquery', 'oro/loading-mask'],
    function ($, LoadingMask) {
        return function (tab) {
            var $tab = $(tab);
            var target = $tab.attr('href');
            if (!target || target === '#' || target.indexOf('javascript') === 0) {
                return;
            }
            var $target = $(target);

            if (!$target.attr('data-loaded') && $target.attr('data-url')) {
                var loadingMask = new LoadingMask();
                loadingMask.render().$el.appendTo($('#container'));
                loadingMask.show();

                $.get($target.attr('data-url'), function(data) {
                    $target.html(data);
                    $target.attr('data-loaded', 1);
                    loadingMask.hide();
                    loadingMask.$el.remove();
                    $target.closest('form').trigger('tab.loaded', $target);
                });
            }
        };
    }
);

