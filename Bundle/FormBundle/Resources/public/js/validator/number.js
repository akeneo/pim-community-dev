/* global define */
define(['underscore', 'oro/translator'],
function (_, __) {
    'use strict';

    var defaultParam = {
        exactMessage: '',
        maxMessage: '',
        minMessage: ''
    };

    /**
     * Check if number fits the range, then returns true, if not - returns one of numbers -1 0 1
     *  -1 - less than minimum
     *   0 - not exact as minimum or maximum (while min and max are equal)
     *   1 - greater than maximum
     *
     * @param {number} number
     * @param {number|undefined} min
     * @param {number|undefined} max
     * @returns {boolean|number}
     */
    function between(number, min, max) {
        var result = true;
        if (!_.isUndefined(min) && min === max) {
            result = number === parseInt(min, 10) || 0;
        } else {
            if (!_.isUndefined(min) && min !== null) {
                result = number >= parseInt(min, 10) || -1;
            }
            if (result === true && !_.isUndefined(max) && max !== null) {
                result = number <= parseInt(max, 10) || 1;
            }
        }
        return result;
    }

    /**
     * @export oro/validator/number
     */
    return [
        'Number',
        function (value, element, param) {
            var result = between(Number(value), param.min, param.max);
            return result === true;
        },
        function (param, element, value) {
            var result,
                message, placeholders = {}, number;
            param = _.extend({}, defaultParam, param);
            value = _.isUndefined(value) ? this.elementValue(element) : value;
            result = between(Number(value), param.min, param.max);
            switch (result) {
                case 0:
                    message = param.exactMessage;
                    number = param.min;
                    break;
                case 1:
                    message = param.maxMessage;
                    number = param.max;
                    break;
                case -1:
                    message = param.minMessage;
                    number = param.min;
                    break;
                default:
                    return '';
            }
            placeholders.limit = number;
            return __(message, placeholders, number);
        }
    ];
});
