$(function() {
    // Disable the oro scrollable container
    $('.scrollable-container').removeClass('scrollable-container').css('overflow', 'visible');

    // Prevent UniformJS from breaking our stuff
    $(document).uniform.restore();

    // Apply Select2
    $('select').select2({ allowClear: true });

    // Apply Select2 multiselect
    $('input.multiselect').select2({ tags: $(this).val() });

    // Toogle accordion icon
    $(document).delegate('.accordion', 'show hide', function(e) {
        $(e.target).siblings('.accordion-heading').find('.accordion-toggle i').toggleClass('fa-icon-collapse-alt fa-icon-expand-alt');
    });

    // Remove bap 'Loading Application' progressbar and partially fix page title regression issue
    document.title = $('#page-title').text();
    if ($('#progressbar').is(':visible')) {
        $('#progressbar').hide();
        $('#page').show();
    }

    $('a[data-dialog]').on('click', function(event) {
        event.preventDefault();
        $el = $(this);
        var message = $el.data('message');
        var title = $el.data('title');
        if ($el.data('dialog') ==  'confirm') {
            var doAction = function() {
                $el.off('click')[0].click();
            };
            PimDialog.confirm(message, title, doAction);
        } else {
            PimDialog.alert(message, title);
        }
    });
});

