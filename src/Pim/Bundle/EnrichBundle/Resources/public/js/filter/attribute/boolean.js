/**
 * Boolean attribute filter.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'jquery',
    'underscore',
    'oro/translator',
    'pim/filter/attribute/attribute',
    'pim/fetcher-registry',
    'pim/user-context',
    'pim/i18n',
    'text!pim/template/filter/attribute/boolean',
    'bootstrap.bootstrapswitch'
], function (
    $,
    _,
    __,
    BaseFilter,
    FetcherRegistry,
    UserContext,
    i18n,
    template
) {
    return BaseFilter.extend({
        shortname: 'boolean',
        template: _.template(template),
        events: {
            'change [name="filter-value"]': 'updateState'
        },

        /**
         * {@inheritdoc}
         */
        renderInput: function (templateContext) {
            var value = this.getValue();
            if (undefined === value) {
                value = true;
            }

            return this.template(_.extend({}, templateContext, {
                value: value,
                field: this.getField(),
                labels: {
                    on: __('switch_on'),
                    off: __('switch_off')
                }
            }));
        },

        /**
         * {@inheritdoc}
         */
        postRender: function () {
            this.$('.switch').bootstrapSwitch();
        },

        /**
         * {@inheritdoc}
         */
        getTemplateContext: function () {
            return $.when(
                BaseFilter.prototype.getTemplateContext.apply(this, arguments),
                FetcherRegistry.getFetcher('attribute').fetch(this.getField())
            ).then(function (templateContext, attribute) {
                return _.extend({}, templateContext, {
                    label: i18n.getLabel(attribute.labels, UserContext.get('uiLocale'), attribute.code)
                });
            }.bind(this));
        },

        /**
         * {@inheritdoc}
         */
        updateState: function () {
            this.setData({
                field: this.getField(),
                operator: '=',
                value: this.$('[name="filter-value"]').is(':checked')
            });
        }
    });
});
