/* global define */
define(['underscore', 'oro/translator', 'oro/formatter/number'],
function (_, __, numberFormatter) {
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
            value = numberFormatter.unformat(value);
            return this.optional(element) ||
                !(isNaN(value) ||
                    (param.min !== null && value < Number(param.min)) ||
                    (param.max !== null && value > Number(param.max)));
        },
        function (param, element) {
            var message, placeholders = {},
                value = numberFormatter.unformat(this.elementValue(element));
            param = _.extend({}, defaultParam, param);
            if (isNaN(value)) {
                message = param.invalidMessage;
            } else if (param.min !== null && value < Number(param.min)) {
                message = param.minMessage;
                placeholders.limit = param.min;
            } else if (param.max !== null && value > Number(param.max)) {
                message = param.maxMessage;
                placeholders.limit = param.max;
            }
            return __(message, placeholders);
        }
    ];
});
