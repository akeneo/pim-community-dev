/* global define */
define(['underscore', 'oro/translator'],
function (_, __) {
    'use strict';

    var between = function (number, min, max) {
            var result = true;
            if (!_.isUndefined(min) && min === max) {
                result = number === parseInt(min, 10) || 0;
            } else {
                if (!_.isUndefined(min)) {
                    result = number >= parseInt(min, 10) || -1;
                }
                if (result === true && !_.isUndefined(max)) {
                    result = number <= parseInt(max, 10) || 1;
                }
            }
            return result;
        },
        defaultMessage = {
            exec: "This value should have exactly {{ limit }} character.|This value should have exactly {{ limit }} characters.",
            max: "This value is too long. It should have {{ limit }} character or less.|This value is too long. It should have {{ limit }} characters or less.",
            min: "This value is too short. It should have {{ limit }} character or more.|This value is too short. It should have {{ limit }} characters or more."
        };

    /**
     * @export oro/validator/length
     */
    return [
        'Length',
        function (value, element, param) {
            var result = between(value.length, param.min, param.max);
            return result === true;
        },
        function (param, element) {
            var value = this.elementValue(element),
                result = between(value.length, param.min, param.max),
                message, placeholders = {}, number;
            switch (result) {
                case 0:
                    message = param.exactMessage || defaultMessage.exec;
                    number = param.min;
                    break;
                case 1:
                    message = param.maxMessage || defaultMessage.max;
                    number = param.max;
                    break;
                case -1:
                    message = param.minMessage || defaultMessage.min;
                    number = param.min;
                    break;
                default:
                    return;
            }
            placeholders.limit = number;
            return __(message, placeholders, number);
        }
    ];
});
