/* global define */
/* jshint browser:true */
define(['jquery', 'underscore', 'oro/translator', 'jquery.validate'],
function ($, _, __) {
    'use strict';

    var defaultParam = {
        invalid_message: 'This value is not valid.',
        invalid_message_parameters: {}
    };

    /**
     * @export oro/validator/repeated
     */
    return [
        'Repeated',
        function (value, element, params) {
            // validator should be added to repeated field (second one)
            var id = element.id.slice(0, -(params.second_name || '').length) + (params.first_name || ''),
                firstElement = document.getElementById(id);
            return this.optional(firstElement) || value === this.elementValue(firstElement);
        },
        function (param) {
            param = _.extend({}, defaultParam, param);
            return __(param.invalid_message, param.invalid_message_parameters);
        }
    ];
});
