$(document).ready(function () {
    $(document).on('click', '.additional-button', function () {
        var additionalInput = $('input[name = "additional_data"]');
        additionalInput.val($(this).attr('data-additional'));
        $('#' + additionalInput.attr('data-form-id')).submit();
    });
});
