(function($, undefined) {
    function confirmDeletion (event) {
        var needConfirmation = !event.currentTarget.hasAttribute('data-no-confirm');

        if (needConfirmation && !confirm($(event.currentTarget).data('confirm') || 'Are you sure?')) {
            return false;
        }

        return true;
    }

    $(document).ready(function() {
        $('body').delegate('a[data-method]', 'click', function (event) {
            event.preventDefault();

            if (!confirmDeletion(event)) {
                return;
            }

            var form      = document.createElement('form');
            var input     = document.createElement('input');

            form.method   = 'POST';
            form.action   = event.currentTarget.href;

            input.type    = 'hidden';
            input.name    = '_method';
            input.value   = $(event.target).data('method');

            form.appendChild(input);

            document.body.appendChild(form);

            form.submit();
        });
    });
})( jQuery );
