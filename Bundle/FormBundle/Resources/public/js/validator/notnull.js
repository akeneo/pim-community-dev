/* global define */
define(['jquery', 'underscore', 'oro/translator', 'jquery.validate'],
function ($, _, __) {
    'use strict';

    var defaultParam = {
        message: 'This value should not be null.'
    };

    /**
     * @export oro/validator/notnull
     */
    return [
        'NotNull',
        function () {
            return $.validator.methods.required.apply(this, arguments);
        },
        function (param) {
            param = _.extend({}, defaultParam, param);
            return __(param.message);
        }
    ];
});
