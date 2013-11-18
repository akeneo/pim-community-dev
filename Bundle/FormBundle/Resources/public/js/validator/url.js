/* global define */
define(['jquery', 'underscore', 'oro/translator', 'jquery.validate'],
function ($, _, __) {
    'use strict';

    var defaultParam = {
        message: 'This value is not a valid URL.'
    };

    /**
     * @export oro/validator/url
     */
    return [
        'Url',
        function () {
            return $.validator.methods.url.apply(this, arguments);
        },
        function (param) {
            param = _.extend({}, defaultParam, param);
            return __(param.message);
        }
    ];
});
