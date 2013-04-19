$(function() {
    // Apply Select2
    $('select').select2({ allowClear: true });

    // Apply Select2 multiselect
    $('input.multiselect').select2({ tags: $(this).val() });

    // Toogle accordion icon
    $(document).delegate('.accordion', 'show hide', function(e) {
        $(e.target).siblings('.accordion-heading').find('.accordion-toggle i').toggleClass('icon-chevron-up icon-chevron-down');
    });
});

