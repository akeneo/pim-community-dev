/* global define */
define(['underscore', 'oro/translator'],
function (_, __) {
    'use strict';

    var defaultParam = {
        minMessage: 'This value should be {{ limit }} or more.',
        maxMessage: 'This value should be {{ limit }} or less.',
        invalidMessage: 'This value should be a valid number.'
    };

    /**
     * @export oro/validator/range
     */
    return [
        'Range',
        function (value, element, param) {
            value = Number(value);
            return this.optional(element) ||
                !(isNaN(value) || value < Number(param.min) || value > Number(param.max));
        },
        function (param, element) {
            var message, placeholders = {},
                value = Number(this.elementValue(element));
            param = _.extend({}, defaultParam, param);
            if (isNaN(value)) {
                message = param.invalidMessage;
            } else if (value < Number(param.min)) {
                message = param.minMessage;
                placeholders.limit = param.min;
            } else if (value > Number(param.max)) {
                message = param.maxMessage;
                placeholders.limit = param.max;
            }
            return __(message, placeholders);
        }
    ];
});
