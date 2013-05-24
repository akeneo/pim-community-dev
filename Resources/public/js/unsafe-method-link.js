(function($, undefined) {
    $(document).ready(function() {
        $('body').delegate('a[data-method]', 'click', function (event) {
            event.preventDefault()
            console.log($(event.currentTarget).data('confirm'));

            PimDialog.confirm(
                $(event.currentTarget).data('confirm') || 'Are you sure?',
                $(event.currentTarget).data('title') || 'Confirmation required',
                function(){ sendRequest(event.currentTarget.href, $(event.currentTarget).data('method')) }
            );

            function sendRequest(action, method) {
                var form    = document.createElement('form');
                var input   = document.createElement('input');

                form.method = 'POST';
                form.action = action;

                input.type  = 'hidden';
                input.name  = '_method';
                input.value = method;

                form.appendChild(input);

                document.body.appendChild(form);

                form.submit();
            }
        });
    });
})( jQuery );
