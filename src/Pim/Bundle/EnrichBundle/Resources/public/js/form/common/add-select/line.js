

/**
 * Common add select line view
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
import $ from 'jquery'
import _ from 'underscore'
import Backbone from 'backbone'
import template from 'pim/template/form/add-select/line'
export default Backbone.View.extend({
    className: '.select2-results',
    template: _.template(template),
    checked: false,
    item: null,

            /**
             * {@inheritdoc}
             */
    initialize: function () {
        this.item = this.options.item
    },

            /**
             * {@inheritdoc}
             */
    render: function () {
        this.$el.html(this.template({
            item:    this.item,
            checked: this.checked
        }))

        return this
    },

            /**
             * Update the checkbox status then render the view
             *
             * @param {bool} checked
             */
    setCheckedCheckbox: function (checked) {
        this.checked = checked

        this.render()
    }
})

