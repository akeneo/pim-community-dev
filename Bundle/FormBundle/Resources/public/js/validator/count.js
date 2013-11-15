/* global define */
define(['underscore', 'oro/validator/number'],
function (_, numberValidator) {
    'use strict';

    var defaultParam = {
        exactMessage: 'This collection should contain exactly {{ limit }} element.|This collection should contain exactly {{ limit }} elements.',
        maxMessage: 'This collection should contain {{ limit }} element or less.|This collection should contain {{ limit }} elements or less.',
        minMessage: 'This collection should contain {{ limit }} element or more.|This collection should contain {{ limit }} elements or more.'
    };

    /**
     * Calculates value
     *
     * For now supports only collection of checkbox with same name
     *
     * @param {$.validator} validator
     * @param {Element} element
     */
    function getCount(validator, element) {
        return validator.findByName(element.name).filter(':checked').length;
    }

    /**
     * @export oro/validator/count
     */
    return [
        'Count',
        function (value, element, param) {
            value = getCount(this, element);
            return numberValidator[1].call(this, value, element, param);
        },
        function (param, element) {
            var value = getCount(this, element);
            param = _.extend({}, defaultParam, param);
            return numberValidator[2].call(this, param, element, value);
        }
    ];
});
