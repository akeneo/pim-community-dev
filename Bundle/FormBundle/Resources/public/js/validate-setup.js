/* global define */
define(['jquery', 'underscore', 'oro/translator', 'oro/tools', 'jquery.validate'],
function($, _, __, tools) {
    'use strict';

    /**
     * Fetches descendant form elements which available for validation
     *
     * @this $.validator
     * @param {Element|jQuery} element
     * @returns {jQuery}
     */
    function elementsOf(element) {
        /*jshint validthis:true */
        return $(element).find("input, select, textarea")
            .not(":submit, :reset, :image, [disabled]")
            .not(this.settings.ignore);
    }

    /**
     * Collects all ancestor elements that have validation rules
     *
     * @param {Element|jQuery} element
     * @returns {Array.<Element>} sorted in order from form element to input element
     */
    function validationHolders(element) {
        var $el = $(element),
            form = $el.parents('form').first(),
            // instance of validator
            validator = $(form).data('validator');
        return _.filter($el.add($el.parentsUntil(form)).add(form).toArray(), function (el) {
            var $el = $(el);
            // is it current element or first in a group of elements
            return $el.data('validation') && ($el.is(element) || elementsOf.call(validator, $el).first().is(element));
        });
    }

    /**
     * Goes across ancestor elements (including itself) and collects validation rules
     *
     * @param {Element|jQuery} element
     * @return {Object} key name of validation rule, value is its options
     */
    function validationsOf(element) {
        var validations = _.map(validationHolders(element), function (el) {
            return $(el).data('validation');
        });
        validations.unshift({});
        return _.extend.apply(null, validations);
    }

    /**
     * Looks for ancestor element (including itself), whose validation rule was violated
     *
     * @param {Element|jQuery} element
     * @param {string=} method by default reads methods name from element's 'data-violated' property
     * @returns {Element}
     */
    function validationBelongs(element, method) {
        method = method || $(element).data('violated');
        return _.find(validationHolders(element).reverse(), function (el) {
            return $(el).data('validation').hasOwnProperty(method);
        });
    }

    // turn off adding rules from attributes
    $.validator.attributeRules = function () {return {};};

    // turn off adding rules from class
    $.validator.classRules = function () {return {};};

    // substitute data rules reader
    $.validator.dataRules = function (element) {
        var rules = {};
        _.each(validationsOf(element), function (param, method) {
            if ($.validator.methods[method]) {
                rules[method] = {param: param};
            } else if ($(element.form).data('validator').settings.debug) {
                console.error('Validation method "' + method + '" does not exist');
            }
        });
        return rules;
    };

    // translates default messages
    $.validator.prototype.defaultMessage = _.wrap($.validator.prototype.defaultMessage, function (func) {
        var message = func.apply(this, _.rest(arguments));
        return _.isString(message) ? __(message) : message;
    });

    // saves name of validation rule which is violated
    $.validator.prototype.formatAndAdd = _.wrap($.validator.prototype.formatAndAdd, function (func, element, rule) {
        $(element).data('violated', rule.method);
        return func.apply(this, _.rest(arguments));
    });

    // updates place for message label before show message
    $.validator.prototype.showLabel = _.wrap($.validator.prototype.showLabel, function (func, element, message) {
        var label = this.errorsFor(element);
        if (message && label.length) {
            this.settings.errorPlacement(label, element);
        }
        return func.apply(this, _.rest(arguments));
    });

    /**
     * Loader for custom validation methods
     *
     * @param {string|Array.<string>} module name of AMD module or list of modules
     */
    $.validator.loadMethod = function (module) {
        tools.loadModules($.makeArray(module), function (validators) {
            _.each(validators, function(args) {
                $.validator.addMethod.apply($.validator, args);
            });
        });
    };

    $.validator.setDefaults({
        errorElement: 'span',
        errorClass: 'validation-faled',
        errorPlacement: function (label, $el) {
            // finds element which validation rule was violated and inserts label after it
            label.insertAfter(validationBelongs($el));
        },
        highlight: function (element) {
            var $target = $(validationBelongs(element));
            this.settings.unhighlight.call(this, element);
            if ($target.parent().is('.selector, .uploader, .input-append, .input-prepend')) {
                $target = $target.parent();
            }
            $target.addClass('error').closest('.controls').addClass('validation-error');
        },
        unhighlight: function(element) {
            $(element).closest('.error').removeClass('error')
                .closest('.controls').removeClass('validation-error');
        }
    });

    // general validation methods
    var methods = [
        'oro/validator/count',
        'oro/validator/date',
        'oro/validator/email',
        'oro/validator/length',
        'oro/validator/notblank',
        'oro/validator/range',
        'oro/validator/regex',
        'oro/validator/url'
    ];
    $.validator.loadMethod(methods);
});
