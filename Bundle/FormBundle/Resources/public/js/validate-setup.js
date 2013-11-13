/* global define */
define(['jquery', 'underscore', 'oro/translator', 'oro/tools', 'jquery.validate'],
function($, _, __, tools) {
    'use strict';

    // turn off adding rules from attributes
    $.validator.attributeRules = function () {return {};};
    // turn off adding rules from class
    $.validator.classRules = function () {return {};};
    // substitute data rules reader
    $.validator.dataRules = function (element) {
        var rules = {};
        _.each($(element).data('validation'), function (param, method) {
            if ($.validator.methods[method]) {
                rules[method] = {param: param};
            } else {
                console.error('Validation method "' + method + '" does not exist');
            }
        });
        return rules;
    };

    $.validator.prototype.defaultMessage = _.wrap($.validator.prototype.defaultMessage, function (func) {
        var message = func.apply(this, _.rest(arguments));
        return _.isString(message) ? __(message) : message;
    });

    $.validator.loadMethod = function (module) {
        tools.loadModules([module], function (validators) {
            _.each(validators, function(args) {
                $.validator.addMethod.apply($.validator, args);
            });
        });
    };

    var validators = [
        'oro/validator/notblank',
        'oro/validator/length',
        'oro/validator/email'
    ];
    _.each(validators, $.validator.loadMethod);
});
