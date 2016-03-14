'use strict';

/**
 * Module used to display the localized properties of a variant group
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
        'underscore',
        'pim/form',
        'pim/fetcher-registry',
        'text!pim/template/variant-group/tab/properties/translation'
    ],
    function (
        _,
        BaseForm,
        FetcherRegistry,
        template
    ) {
        return BaseForm.extend({
            className: 'accordion-group translation-container',
            template: _.template(template),
            events: {
                'change .label-field': 'updateModel'
            },
            validationErrors: {},

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:pre_save', this.onPreSave);
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:bad_request', this.onValidationError);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * Pre save callback
             */
            onPreSave: function () {
                this.validationErrors = {};

                this.render();
            },

            /**
             * On validation callback
             *
             * @param {Event} event
             */
            onValidationError: function (event) {
                this.validationErrors = event.response.translations ? event.response.translations : {};

                this.render();
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                FetcherRegistry.getFetcher('locale').fetchAll().then(function (locales) {
                    this.$el.html(this.template({
                        model: this.getFormData(),
                        locales: locales,
                        errors: this.validationErrors
                    }));
                }.bind(this));

                this.renderExtensions();
            },

            /**
             * @param {Object} event
             */
            updateModel: function (event) {
                var data = this.getFormData();

                data.labels[event.target.dataset.locale] = event.target.value;

                this.setData(data);
            }
        });
    }
);
