/* global define */
define(['jquery', 'jquery.validate'],
function ($) {
    'use strict';

    /**
     * @export oro/validator/notblank
     */
    return [
        'NotBlank',
        function () {
            return $.validator.methods.required.apply(this, arguments);
        },
        'This value should not be blank.'
    ];
});
