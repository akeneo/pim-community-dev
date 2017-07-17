/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'jquery',
    'underscore',
    'oro/translator',
    'pim/attribute-edit-form/properties/field',
    'pim/fetcher-registry',
    'pim/user-context',
    'pim/i18n',
    'pim/template/attribute/tab/properties/group'
],
function (
    $,
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
                groups: _.sortBy(this.attributeGroups, 'sort_order'),
                i18n: i18n,
                locale: UserContext.get('catalogLocale'),
                labels: {
                    defaultLabel: __('pim_enrich.form.attribute.tab.properties.default_label.group')
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
         * {@inheritdoc}
         */
        getFieldValue: function (field) {
            return $(field).val();
        }
    });
});
