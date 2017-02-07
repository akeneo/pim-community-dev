'use strict';

/**
 * todo-a2x: implement
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/fetcher-registry',
        'pim/i18n',
        'pim/user-context',
        'text!pim/template/family/tab/general/attribute-as-label',
        'jquery.select2'
    ],
    function (
        $,
        _,
        __,
        BaseForm,
        FetcherRegistry,
        i18n,
        UserContext,
        template
    ) {
        return BaseForm.extend({
            className: 'select',
            template: _.template(template),
            errors: [],
            attributes: null,
            catalogLocale: UserContext.get('catalogLocale'),

            /**
             * {@inheritdoc}
             */
            initialize: function (config) {
                this.config = config.config;
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.configured) {
                    return this;
                }

                if (!this.attributes) {
                    $.when(
                        FetcherRegistry.getFetcher('attribute').search({
                            types: 'pim_catalog_identifier,pim_catalog_text'
                        })
                    ).then(function (attributes) {
                        this.attributes = attributes;
                        return this.render();
                    }.bind(this));

                    return this;
                }

                this.$el.html(this.template({
                    i18n: i18n,
                    catalogLocale: this.catalogLocale,
                    attributes: this.attributes,
                    currentAttribute: this.getFormData().attribute_as_label,
                    fieldBaseId: this.config.fieldBaseId,
                    errors: this.errors,
                    label: __(this.config.label),
                    requiredLabel: __('pim_enrich.form.required')
                }));

                this.$('.select2').select2().on('change', this.updateState.bind(this));

                this.renderExtensions();
            },

            /**
             * Update object state on property change
             *
             * @param event
             */
            updateState: function (event) {
                var data = this.getFormData();
                data.attribute_as_label = event.currentTarget.value;
                this.setData(data);
            }
        });
    }
);
