/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
define([
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/fetcher-registry',
        'text!pim/template/attribute/tab/properties/common',
        'jquery.select2'
    ],
    function (
        _,
        __,
        BaseForm,
        fetcherRegistry,
        template
    ) {
        return BaseForm.extend({
            className: 'tabsection',
            template: _.template(template),
            config: {},
            events: {
                'change input, select': function (event) {
                    this.updateModel(event.target);
                    this.render();
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
                    'group_code',
                    'scopable',
                    'localizable',
                    'is_locale_specific',
                    'useable_as_grid_filter'
                ];
                var fieldLabels = _.object(properties, _.map(properties, function (propertyName) {
                    return __('pim_enrich.form.attribute.tab.properties.' + propertyName);
                }));

                var attribute = this.getFormData();

                fetcherRegistry.getFetcher('locale').fetchActivated().then(function (availableLocales) {
                    this.$el.html(this.template({
                        mode: this.config.mode,
                        attribute: attribute,
                        availableLocales: availableLocales,
                        labels: {
                            sectionTitle: __('pim_enrich.form.attribute.tab.properties.common'),
                            fields: fieldLabels,
                            type: __('pim_enrich.entity.attribute.type.' + attribute.type),
                            required: __('pim_enrich.form.required'),
                            on: __('switch_on'),
                            off: __('switch_off')
                        }
                    }));

                    this.$('select.select2').select2();
                    this.$('.switch').bootstrapSwitch();

                    this.renderExtensions();
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
    }
);
