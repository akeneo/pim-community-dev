'use strict';
/**
 * Working copy extension
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
define(
    [
        'jquery',
        'underscore',
        'backbone',
        'oro/mediator',
        'pim/form',
        'pim/attribute-manager',
        'text!pimee/template/product/tab/attribute/modified-by-draft'
    ],
    function (
        $,
        _,
        Backbone,
        mediator,
        BaseForm,
        AttributeManager,
        modifiedByDraftTemplate
    ) {
        return BaseForm.extend({
            modifiedByDraftTemplate: _.template(modifiedByDraftTemplate),

            /**
             * @inheritdoc
             */
            configure: function () {
                this.listenTo(mediator, 'field:extension:add', this.addFieldExtension);
                this.listenTo(mediator, 'pim_enrich:form:field:can_be_copied', this.canBeCopied);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * Return the current working copy
             *
             * @returns {Object|null}
             */
            getWorkingCopy: function () {
                var workingCopy = this.getFormData().meta.working_copy;

                return _.isEmpty(workingCopy) ? null : workingCopy;
            },

            /**
             * Mark a field as "modified by draft" if necessary
             *
             * @param {Object} event
             *
             * @returns {Object}
             */
            addFieldExtension: function (event) {
                var field = event.field;

                if (this.isValueChanged(field)) {
                    var $element = $(this.modifiedByDraftTemplate());
                    $element.on('click', this.showWorkingCopy);

                    field.addElement('label', 'modified_by_draft', $element);
                }

                return this;
            },

            /**
             * Check if the specified field's value has been modified compared to the working copy
             *
             * @param {Object} field
             * @param {string} locale
             * @param {string} scope
             *
             * @returns {boolean}
             */
            isValueChanged: function (field, locale, scope) {
                if (null === this.getWorkingCopy()) {
                    return false;
                }

                var attribute = field.attribute;
                locale = locale || field.context.locale;
                scope = scope || field.context.scope;

                var workingCopyValue = AttributeManager.getValue(
                    this.getWorkingCopy().values[attribute.code],
                    attribute,
                    locale,
                    scope
                );

                if (_.isUndefined(workingCopyValue)) {
                    return false;
                }

                var currentValue = AttributeManager.getValue(
                    this.getFormData().values[attribute.code],
                    attribute,
                    locale,
                    scope
                );

                if (_.isObject(currentValue.data)) {
                    return !_.isEqual(workingCopyValue.data, currentValue.data);
                } else {
                    return workingCopyValue.data !== currentValue.data;
                }
            },

            /**
             * Trigger an event to open the working copy panel
             */
            showWorkingCopy: function () {
                mediator.trigger('draft:action:show_working_copy');
            },

            /**
             * Add the possibility to a field to be copied if its value has been modified in draft
             *
             * @param event
             */
            canBeCopied: function (event) {
                var isValueChanged = this.isValueChanged(event.field, event.locale, event.scope);
                event.canBeCopied = event.canBeCopied || isValueChanged;
            }
        });
    }
);
