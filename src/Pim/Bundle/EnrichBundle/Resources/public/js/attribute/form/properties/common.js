/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'underscore',
    'oro/translator',
    'pim/form',
    'pim/fetcher-registry',
    'pim/user-context',
    'pim/i18n',
    'text!pim/template/attribute/tab/properties/common',
    'jquery.select2'
],
function (
    _,
    __,
    BaseForm,
    fetcherRegistry,
    UserContext,
    i18n,
    template
) {
    return BaseForm.extend({
        className: 'tabsection',
        template: _.template(template),
        config: {},
        events: {
            'change input, select': function (event) {
                this.updateModel(event.target);
                this.getRoot().render();
            }
        },

        /**
         * {@inheritdoc}
         */
        initialize: function (meta) {
            this.config = _.extend({mode: 'edit'}, meta.config);

            BaseForm.prototype.initialize.apply(this, arguments);
        },

        /**
         * {@inheritdoc}
         */
        render: function () {
            var properties = [
                'code',
                'type',
                'group',
                'scopable',
                'localizable',
                'is_locale_specific',
                'useable_as_grid_filter'
            ];
            var fieldLabels = _.object(properties, _.map(properties, function (propertyName) {
                return __('pim_enrich.form.attribute.tab.properties.' + propertyName);
            }));

            var attribute = this.getFormData();

            $.when(
                fetcherRegistry.getFetcher('locale').fetchActivated(),
                fetcherRegistry.getFetcher('attribute-group').fetchAll()
            )
            .then(function (availableLocales, attributeGroups) {
                this.$el.html(this.template({
                    mode: this.config.mode,
                    attribute: attribute,
                    availableLocales: availableLocales,
                    attributeGroups: attributeGroups,
                    labels: {
                        sectionTitle: __('pim_enrich.form.attribute.tab.properties.common'),
                        fields: fieldLabels,
                        type: __('pim_enrich.entity.attribute.type.' + attribute.type),
                        defaultGroup: __('pim_enrich.entity.attribute.group.default_value'),
                        required: __('pim_enrich.form.required'),
                        on: __('switch_on'),
                        off: __('switch_off')
                    },
                    currentLocale: UserContext.get('catalogLocale'),
                    i18n: i18n
                }));

                this.$('select.select2').select2();
                this.$('.switch').bootstrapSwitch();

                this.renderExtensions()
                this.delegateEvents();
            }.bind(this));
        },

        /**
         * Updates the attribute model with new value coming from the updated field.
         *
         * @param {Object} field
         */
        updateModel: function (field) {
            var value = 'checkbox' === field.type ? $(field).is(':checked') : $(field).val();
            var newData = {};
            newData[field.name] = value;

            if ('is_locale_specific' === field.name && false === value) {
                newData.available_locales = [];
            }

            this.setData(newData);
        }
    });
});
