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
    'text!pim/template/attribute/tab/properties/select'
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
        className: 'AknFieldContainer',
        template: _.template(template),
        fieldName: 'group',
        events: {
            'change select': function (event) {
                this.updateModel(event.target);
                this.getRoot().render();
            }
        },

        render: function () {
            fetcherRegistry.getFetcher('attribute-group').fetchAll()
                .then(function (attributeGroups) {
                    this.$el.html(this.template({
                        value: this.getFormData()[this.fieldName],
                        fieldName: this.fieldName,
                        choices: this.formatChoices(attributeGroups, UserContext.get('catalogLocale')),
                        labels: {
                            field: __('pim_enrich.form.attribute.tab.properties.' + this.fieldName),
                            required: __('pim_enrich.form.required'),
                            defaultLabel: __('pim_enrich.entity.attribute.default_metric_unit.default_value')
                        },
                        multiple: false
                    }));

                    this.$('select.select2').select2();

                    this.renderExtensions();
                    this.delegateEvents();
                }.bind(this));
        },

        /**
         * @param {Object} field
         */
        updateModel: function (field) {
            var newData = {};
            newData[this.fieldName] = $(field).val();

            this.setData(newData);
        },

        /**
         * @param {Object} attributeGroups
         * @param {String} currentLocale
         */
        formatChoices: function (attributeGroups, currentLocale) {
            return _.mapObject(attributeGroups, function (group) {
                return i18n.getLabel(group.labels, currentLocale, group.code);
            });
        }
    });
});
