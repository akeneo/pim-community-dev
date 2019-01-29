/**
 * Abstract attribute form field.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'jquery',
    'underscore',
    'oro/translator',
    'pim/form',
    'pim/common/property',
    'pim/common/tab',
    'pim/template/form/common/fields/field'
], function (
    $,
    _,
    __,
    BaseForm,
    propertyAccessor,
    Tab,
    template
) {
    return BaseForm.extend({
        className: 'AknFieldContainer',
        containerTemplate: _.template(template),
        config: {},
        elements: {},
        fieldName: null,
        errors: [],
        warnings: [],
        readOnly: false,

        /**
         * {@inheritdoc}
         */
        initialize(meta) {
            this.config = meta.config;

            if (undefined === this.config.fieldName) {
                throw new Error('This view must be configured with a field name.');
            }

            this.fieldName = this.config.fieldName;
            this.readOnly = this.config.readOnly || false;
            this.errors = [];
            this.warnings = [];

            BaseForm.prototype.initialize.apply(this, arguments);
        },

        /**
         * {@inheritdoc}
         */
        configure() {
            this.listenTo(this.getRoot(), 'pim_enrich:form:entity:bad_request', this.onBadRequest.bind(this));
            this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_save', this.render.bind(this));

            return BaseForm.prototype.configure.apply(this, arguments);
        },

        /**
         * @param {Object} event
         */
        onBadRequest(event) {
            this.errors = this.getFieldErrors(event.response);
            this.render();

            if (this.errors.length) {
                this.getRoot().trigger('pim_enrich:form:form-tabs:change', this.getTabCode());
            }
        },

        /**
         * Filter errors to return only the ones related to this field.
         *
         * @param {Array} errors
         *
         * @returns {Array}
         */
        getFieldErrors(errors) {
            return errors.filter((error) => {
                const fieldNameParts = this.fieldName.split('.');
                const lastPart = fieldNameParts[fieldNameParts.length - 1];
                if (error.path === undefined) {
                    return lastPart === error.attribute || lastPart === undefined;
                }
                const splittedParts = error.path.split(/\[|\]/).filter(part => {
                    return part !== '';
                });

                return lastPart === error.path ||
                    lastPart === error.attribute ||
                    JSON.stringify(fieldNameParts) === JSON.stringify(splittedParts);
            });
        },

        /**
         * Recursively search for the first tab ancestor if any, and returns its code. Sorry.
         *
         * @returns {String}
         */
        getTabCode() {
            let parent = this.getParent();
            while (!(parent instanceof Tab)) {
                parent = parent.getParent();
                if (null === parent) {
                    return null;
                }
            }

            return parent.code;
        },

        /**
         * Renders the container template.
         */
        render() {
            if (!this.isVisible()) {
                this.$el.empty();

                return;
            }

            this.getTemplateContext().then(function (templateContext) {
                this.$el.html(this.containerTemplate(templateContext));
                this.$('.field-input').replaceWith(this.renderInput(templateContext));

                this.postRender(templateContext);
                this.renderExtensions();
                this.delegateEvents();
            }.bind(this));

            return this;
        },

        /**
         * Returns the context params that will be passed to templates.
         *
         * @returns {Promise}
         */
        getTemplateContext() {
            const templateParams = this.config.templateParams || {};

            return $.Deferred()
                .resolve(Object.assign(
                    {},
                    {
                        fieldLabel: this.getLabel(),
                        requiredLabel: this.getRequiredLabel(),
                        fieldName: this.fieldName,
                        fieldId: this.getFieldId(),
                        errors: this.errors,
                        warnings: this.warnings,
                        readOnly: this.isReadOnly(),
                        required: this.config.required || false,
                        __: __
                    },
                    templateParams
                ))
                .promise();
        },

        /**
         * Renders the input inside the field container.
         */
        renderInput() {
            throw new Error('Please implement the renderInput() method in your concrete field class.');
        },

        /**
         * Called after rendering the input.
         */
        postRender() {},

        /**
         * @returns {string}
         */
        getLabel() {
            return undefined === this.config.label
                ? '[' + this.fieldName + ']'
                : __(this.config.label);
        },

        /**
         * @returns {string}
         */
        getRequiredLabel() {
            return undefined === this.config.requiredLabel
                ? __('pim_common.required_label')
                : __(this.config.requiredLabel);
        },

        /**
         * Should the field be displayed?
         *
         * @returns {Boolean}
         */
        isVisible() {
            return true;
        },

        /**
         * Should the field be in readonly mode?
         *
         * @returns {Boolean}
         */
        isReadOnly() {
            return this.readOnly;
        },

        /**
         * Sets the param readOnly of the field
         *
         * @param {Boolean} readOnly
         */
        setReadOnly(readOnly) {
            this.readOnly = Boolean(readOnly);
        },

        /**
         * Receives the new value and updates the data model with it.
         *
         * @param {*} value
         */
        updateModel(value) {
            const data = this.getFormData();
            propertyAccessor.updateProperty(data, this.fieldName, value);

            this.setData(data);
        },

        /**
         * Reads and returns the field value from the model.
         *
         * @returns {*}
         */
        getModelValue() {
            const value = propertyAccessor.accessProperty(
                this.getFormData(),
                this.fieldName
            );

            return null === value ? undefined : value;
        },

        /**
         * Returns a pseudo-unique code used as reference inside templates (as "for" attributes values for example).
         *
         * @returns {String}
         */
        getFieldId() {
            return Math.random().toString(10).substring(2);
        }
    });
});
