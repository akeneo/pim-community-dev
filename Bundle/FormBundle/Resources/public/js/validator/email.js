/* global define */
define(['jquery', 'jquery.validate'],
function ($) {
    'use strict';

    /**
     * @export oro/validator/email
     */
    return [
        'Email',
        function () {
            // @TODO add support of MX check action
            return $.validator.methods.email.apply(this, arguments);
        },
        'This value is not a valid email address.'
    ];
});
