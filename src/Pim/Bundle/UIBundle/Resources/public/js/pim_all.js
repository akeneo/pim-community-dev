$(function() {
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
});

