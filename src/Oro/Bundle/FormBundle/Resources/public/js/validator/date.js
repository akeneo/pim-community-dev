/* global define */
define(['underscore', 'oro/translator'],
function (_, __) {
    'use strict';

    var defaultParam = {
        message: 'This value is not a valid date.'
    };

    /**
     * @export oro/validator/date
     */
    return [
        'Date',
        function (value, element) {
            return value;
            return this.optional(element) || datetimeFormatter.isDateValid(String(value));
        },
        function (param) {
            param = _.extend({}, defaultParam, param);
            return __(param.message);
        }
    ];
});
