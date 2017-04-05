'use strict';

/**
 * Attributes used as label field view
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
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
        'pim/security-context',
        'text!pim/template/family/tab/general/attribute-as-image',
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
        SecurityContext,
        template
    ) {
        return BaseForm.extend({
            className: 'AknFieldContainer',
            template: _.template(template),
            errors: [],
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

                this.$el.html(this.template({
                    i18n: i18n,
                    catalogLocale: this.catalogLocale,
                    attributes: _.filter(
                        this.getFormData().attributes,
                        function (attribute) {
                            return attribute.type === 'pim_catalog_image';
                        }
                    ),
                    currentAttribute: this.getFormData().attribute_as_image,
                    fieldBaseId: this.config.fieldBaseId,
                    errors: this.errors,
                    label: __(this.config.label),
                    emptyLabel: __(this.config.emptyLabel),
                    isReadOnly: !SecurityContext.isGranted('pim_enrich_family_edit_properties')
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
                var value = event.currentTarget.value;
                data.attribute_as_image = ('no_attribute_as_image' === value) ? null : event.currentTarget.value;
                this.setData(data);
            }
        });
    }
);
