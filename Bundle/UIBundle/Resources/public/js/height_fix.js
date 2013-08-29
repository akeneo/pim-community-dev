/* dynamic height for central column */
jQuery.expr[':'].parents = function(a, i, m){
    return jQuery(a).parents(m[3]).length < 1;
};

$(document).ready(function () {
    var debugBar = $('.sf-toolbar');
    var anchor = $('#bottom-anchor');
    var content = false;
    if (!anchor.length) {
        anchor = $('<div id="bottom-anchor"/>')
            .css({
                position: 'fixed',
                bottom: '0',
                left: '0',
                width: '1px',
                height: '1px'
            })
            .appendTo($(document.body));
    }

    var initializeContent = function() {
        if (!content) {
            content = $('.scrollable-container').filter(':parents(.ui-widget)');
            content.css('overflow', 'auto');

            $('.scrollable-substructure').css({
                'padding-bottom': '0px',
                'margin-bottom': '0px'
            });
        }
    };

    var adjustHeight = function() {
        initializeContent();
        var debugBarHeight = debugBar.length && debugBar.is(':visible') ? debugBar.height() : 0;
        var anchorTop = anchor.position().top;
        content.each(function(pos, el) {
            el = $(el);
            el.height(anchorTop - el.position().top - debugBarHeight);
        });
    };

    var tries = 0;
    var waitForDebugBar = function()
    {
        if (debugBar.children().length) {
            window.setTimeout(adjustHeight, 500);
        } else if (tries < 100) {
            tries++;
            window.setTimeout(waitForDebugBar, 500);
        }
    }

    var adjustReloaded = function() {
        content = false;
        adjustHeight();
    };

    debugBar.length ?  waitForDebugBar() : adjustHeight();
    $(window).on('resize', adjustHeight);
    Oro.Events.bind("hash_navigation_request:complete", adjustReloaded);
});
