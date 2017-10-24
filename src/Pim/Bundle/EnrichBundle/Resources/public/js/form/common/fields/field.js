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
    'pim/common/tab',
    'pim/template/form/common/fields/field'
], function (
    $,
    _,
    __,
    BaseForm,
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

        /**
         * {@inheritdoc}
         */
        initialize: function (meta) {
            this.config = meta.config;

            if (undefined === this.config.fieldName) {
                throw new Error('This view must be configured with a field name.');
            }

            this.fieldName = this.config.fieldName;

            BaseForm.prototype.initialize.apply(this, arguments);
        },

        /**
         * {@inheritdoc}
         */
        configure: function () {
            this.listenTo(this.getRoot(), 'pim_enrich:form:entity:bad_request', this.onBadRequest.bind(this));

            return BaseForm.prototype.configure.apply(this, arguments);
        },

        /**
         * @param {Object} event
         */
        onBadRequest: function (event) {
            this.errors = _.where(event.response, {path: this.fieldName});
            this.render();

            this.getRoot().trigger('pim_enrich:form:form-tabs:change', this.getTabCode());
        },

        /**
         * Recursively search for the first tab ancestor if any, and returns its code. Sorry.
         *
         * @returns {String}
         */
        getTabCode: function () {
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
        render: function () {
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
        },

        /**
         * Returns the context params that will be passed to templates.
         *
         * @returns {Promise}
         */
        getTemplateContext: function () {
            return $.Deferred()
                .resolve({
                    fieldLabel: __('pim_enrich.form.attribute.tab.properties.label.' + this.fieldName),
                    requiredLabel: __('pim_enrich.form.required'),
                    fieldName: this.fieldName,
                    fieldId: this.getFieldId(),
                    errors: this.errors,
                    readOnly: this.isReadOnly(),
                    required: this.config.required || false,
                    __: __
                })
                .promise();
        },

        /**
         * Renders the input inside the field container.
         */
        renderInput: function () {
            throw new Error('Please implement the renderInput() method in your concrete field class.');
        },

        /**
         * Called after rendering the input.
         */
        postRender: function () {},

        /**
         * Should the field be displayed?
         *
         * @returns {Boolean}
         */
        isVisible: function () {
            return true;
        },

        /**
         * Should the field be in readonly mode?
         *
         * @returns {Boolean}
         */
        isReadOnly: function () {
            return this.config.readOnly || false;
        },

        /**
         * Receives the new value and updates the data model with it.
         *
         * @param {*} value
         */
        updateModel: function (value) {
            const newData = {};
            newData[this.fieldName] = value;

            this.setData(newData);
        },

        /**
         * Returns a pseudo-unique code used as reference inside templates (as "for" attributes values for example).
         *
         * @returns {String}
         */
        getFieldId: function () {
            return Math.random().toString(10).substring(2);
        }
    });
});
