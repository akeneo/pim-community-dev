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
            workingCopy: null,

            /**
             * @inheritdoc
             */
            configure: function () {
                this.listenTo(mediator, 'product:action:post_fetch', this.onProductPostFetch);
                this.listenTo(mediator, 'product:action:pre_update', this.onProductPreUpdate);
                this.listenTo(mediator, 'field:extension:add', this.addFieldExtension);
                this.listenTo(mediator, 'pim_enrich:form:field:can_be_copied', this.canBeCopied);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * Event callback called just after product is fetched form backend
             *
             * @param {Object} product
             */
            onProductPostFetch: function (product) {
                this.workingCopy = product.meta.working_copy;
            },

            /**
             * Update working copy after saving
             *
             * @param {Object} product
             */
            onProductPreUpdate: function (product) {
                this.workingCopy = product.meta.working_copy;
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
                if (!this.workingCopy) {
                    return false;
                }

                var attribute = field.attribute;
                locale = locale || field.context.locale;
                scope = scope || field.context.scope;

                var workingCopyValue = AttributeManager.getValue(
                    this.workingCopy.values[attribute.code],
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
