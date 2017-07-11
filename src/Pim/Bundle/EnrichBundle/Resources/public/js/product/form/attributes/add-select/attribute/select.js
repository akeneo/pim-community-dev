

/**
 * Product add attribute select extension view
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import $ from 'jquery'
import _ from 'underscore'
import __ from 'oro/translator'
import BaseAddSelect from 'pim/common/add-select'
import LineView from 'pim/product/add-select/attribute/line'
import FetcherRegistry from 'pim/fetcher-registry'
import AttributeManager from 'pim/attribute-manager'
import ChoicesFormatter from 'pim/formatter/choices/base'
export default BaseAddSelect.extend({
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
        return AttributeManager.getAttributes(this.getFormData())
    },

            /**
             * {@inheritdoc}
             */
    prepareChoices: function (items) {
        return _.chain(items).map(function (item) {
            var group = ChoicesFormatter.formatOne(item.group)
            var choice = ChoicesFormatter.formatOne(item)
            choice.group = group

            return choice
        }).value()
    },

            /**
             * Triggers configured event with items codes selected
             */
    addItems: function () {
        this.trigger(this.addEvent, { codes: this.selection })
    }
})


