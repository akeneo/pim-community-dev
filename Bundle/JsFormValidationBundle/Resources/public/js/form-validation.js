/* global define */
define(['jquery', 'underscore', 'oro/translator', 'oro/app'],
function($, _, __, app) {
    'use strict';

    return {
        /**
         * Shows errors represent by the given err object.
         *
         * @param container {jQuery|string} The jQuery object or jQuery selector for a element contains form fields.
         *                                  It may be form element itself or any element contains the form
         * @param err {string|Object} Can be a string, exception object or an object represents JSON REST response
         * @param formFieldPrefix {string} [optional] A prefix of form field id. If it is not specified the form id
         *                                  will be used as the prefix.
         */
        handleErrors: function (container, err, formFieldPrefix) {
            if (_.isString(container)) {
                container = $(container);
            }
            this.removeErrors(container);
            var errors = [];
            if (_.isString(err)) {
                // string
                errors.push(err);
            } if (!_.isUndefined(err.message)) {
                // exception object
                if (app.debug) {
                    errors.push(err.message);
                    if (!_.isUndefined(console)) {
                        console.error(_.isUndefined(err.stack) ? err : err.stack);
                    }
                } else {
                    errors.push(__('Unexpected error was occurred'));
                }
            }
            if (!_.isUndefined(err.errors) && _.isArray(err.errors)) {
                // JSON REST response
                _.each(err.errors, function(value) { errors.push(__(value)); });
            }
            this.addErrors(container, errors);

            if (!_.isUndefined(err.children)) {
                // JSON REST response
                if (_.isUndefined(formFieldPrefix)) {
                    formFieldPrefix = this.getFormFieldPrefix(container);
                }
                _.each(err.children,
                    _.bind(function(value, key) {
                        var field = container.find('#' + formFieldPrefix + key);
                        this.removeFieldErrors(field);

                        if (!_.isUndefined(value.errors) && _.isArray(value.errors)) {
                            this.addFieldErrors(field,
                                _.map(value.errors, function(val) { return __(val) }));
                        }
                    }, this));
                this.setFocusOnFirstErrorField(container);
            }
        },

        /**
         * Gets a prefix of form field id. Usually it equal to form id ends with underscore character (_).
         *
         * @param container {jQuery|string} The jQuery object or jQuery selector for a element contains form fields.
         *                                  It may be form element itself or any element contains the form
         */
        getFormFieldPrefix: function (container) {
            var formFieldPrefix = '';
            var form = null;
            if (container.prop("tagName").toLowerCase() === 'form') {
                form = container;
            } else {
                form = container.find('form');
            }
            if (!_.isNull(form)) {
                formFieldPrefix = form.attr('id');
                if (!_.isUndefined(formFieldPrefix)) {
                    formFieldPrefix += '_';
                } else {
                    formFieldPrefix = '';
                }
            }
            return formFieldPrefix;
        },

        /**
         * Removes all field level errors.
         *
         * @param field {jQuery|string} The jQuery object or jQuery selector for a form field element.
         */
        removeFieldErrors: function (field) {
            var target = _.isString(field) ? $(field) : field;
            if (target.parent().filter('.error').length) {
                target = target.parent();
            }
            target.siblings('.validation-faled').remove();
            target.removeClass('error').parent().filter('.controls').removeClass('validation-error');
        },

        /**
         * Adds field level errors.
         *
         * @param field {jQuery|string} The jQuery object or jQuery selector for a form field element.
         * @param errorMessages {string[]|string} The localized error string(s).
         */
        addFieldErrors: function (field, errorMessages) {
            var target = _.isString(field) ? $(field) : field;
            if (target.parent().filter('.selector').length || target.parent().filter('.uploader').length) {
                target = target.parent();
            }
            if (!target.siblings('.validation-faled').length) {
                $('<span class="validation-faled"></span>').insertAfter(target);
            }
            target.siblings('.validation-faled').text(
                _.isArray(errorMessages) ? errorMessages.join('; ') : errorMessages);
            target.addClass('error').parent().filter('.controls').addClass('validation-error');
        },

        /**
         * Removes all form level errors.
         *
         * @param container {jQuery|string} The jQuery object or jQuery selector for a element contains form fields.
         *                                  It may be form element itself or any element contains the form
         */
        removeErrors: function (container) {
            if (_.isString(container)) {
                container = $(container);
            }
            var errorContainer = container.find('.alert-error');
            if (errorContainer.length > 0) {
                errorContainer.hide();
                var errorList = errorContainer.find('ul');
                errorList.empty();
            }
        },

        /**
         * Adds form level errors.
         *
         * @param container {jQuery|string} The jQuery object or jQuery selector for a element contains form fields.
         *                                  It may be form element itself or any element contains the form
         * @param errorMessages {string[]|string} The localized error string(s).
         */
        addErrors: function (container, errorMessages) {
            if (_.isString(container)) {
                container = $(container);
            }
            var errorContainer = container.find('.alert-error');
            if (errorContainer.length > 0) {
                var errorList = errorContainer.find('ul');
                if (_.size(errorMessages) > 0) {
                    if (errorList.length === 0) {
                        errorList = $('<ul>');
                        errorContainer.append(errorList);
                    }
                    _.each(errorMessages,
                        function(value) {
                            this.append($('<li>').text(value));
                        },
                        errorList);
                    errorContainer.show();
                }
            }
        },

        /**
         * Sets the focus to the first erroneous form field.
         *
         * @param container {jQuery|string} The jQuery object or jQuery selector for a element contains form fields.
         *                                  It may be form element itself or any element contains the form
         */
        setFocusOnFirstErrorField: function (container) {
            if (_.isString(container)) {
                container = $(container);
            }
            container.find('.error>:input,:input.error').first().focus();
        }
    }
});
