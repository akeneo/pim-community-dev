'use strict';

/**
 * Product add attribute select extension view
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/common/add-select',
        'pim/product/add-select/attribute/line',
        'pim/fetcher-registry',
        'pim/attribute-manager',
        'pim/formatter/choices/base'
    ],
    function (
        $,
        _,
        __,
        BaseAddSelect,
        LineView,
        FetcherRegistry,
        AttributeManager,
        ChoicesFormatter
    ) {
        return BaseAddSelect.extend({
            className: 'add-attribute',
            lineView: LineView,
            defaultConfig: {
                select2: {
                    placeholder: 'pim_enrich.form.common.tab.attributes.btn.add_attributes',
                    title: 'pim_enrich.form.common.tab.attributes.info.search_attributes',
                    buttonTitle: 'pim_enrich.form.common.tab.attributes.btn.add',
                    countTitle: 'pim_enrich.form.product.tab.attributes.info.attributes_selected',
                    emptyText: 'pim_enrich.form.common.tab.attributes.info.no_available_attributes',
                    classes: 'pim-add-attributes-multiselect',
                    minimumInputLength: 0,
                    dropdownCssClass: 'add-attribute',
                    closeOnSelect: false
                },
                resultsPerPage: 10,
                searchParameters: {options: {exclude_unique: true}},
                mainFetcher: 'attribute',
                events: {
                    disable: null,
                    enable: null,
                    add: 'add-attribute:add'
                }
            },

            /**
             * {@inheritdoc}
             */
            getItemsToExclude: function () {
                return AttributeManager.getAttributes(this.getFormData());
            },

            /**
             * {@inheritdoc}
             */
            prepareChoices: function (items) {
                return _.chain(items).map(function (item) {
                    var group = ChoicesFormatter.formatOne(item.group);
                    var choice = ChoicesFormatter.formatOne(item);
                    choice.group = group;

                    return choice;
                }).value();
            },

            /**
             * Triggers configured event with items codes selected
             */
            addItems: function () {
                this.trigger(this.addEvent, { codes: this.selection });
            }
        });
    }
);

