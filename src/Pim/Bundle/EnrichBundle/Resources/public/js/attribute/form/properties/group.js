/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'underscore',
    'oro/translator',
    'pim/attribute-edit-form/properties/field',
    'pim/fetcher-registry',
    'pim/user-context',
    'pim/i18n',
    'text!pim/template/attribute/tab/properties/select'
],
function (
    _,
    __,
    BaseField,
    fetcherRegistry,
    UserContext,
    i18n,
    template
) {
    return BaseField.extend({
        template: _.template(template),
        attributeGroups: {},

        /**
         * {@inheritdoc}
         */
        configure: function () {
            return $.when(
                BaseField.prototype.configure.apply(this, arguments),
                fetcherRegistry.getFetcher('attribute-group').fetchAll()
                    .then(function (attributeGroups) {
                        this.attributeGroups = attributeGroups;
                    }.bind(this))
            );
        },

        /**
         * {@inheritdoc}
         */
        renderInput: function (templateContext) {
            return this.template(_.extend(templateContext, {
                value: this.getFormData()[this.fieldName],
                choices: this.formatChoices(this.attributeGroups, UserContext.get('catalogLocale')),
                multiple: false,
                labels: {
                    defaultLabel: __('pim_enrich.entity.attribute.group.default_value')
                }
            }));
        },

        /**
         * {@inheritdoc}
         */
        postRender: function () {
            this.$('select.select2').select2();
        },

        /**
         * @param {Object} attributeGroups
         * @param {String} currentLocale
         */
        formatChoices: function (attributeGroups, currentLocale) {
            return _.mapObject(attributeGroups, function (group) {
                return i18n.getLabel(group.labels, currentLocale, group.code);
            });
        },

        /**
         * {@inheritdoc}
         */
        getFieldValue: function (field) {
            return $(field).val();
        }
    });
});
