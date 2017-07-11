

/**
 * Module to display a line in the Select2 dropdown of the Datagrid View Selector.
 * This module accepts extensions to display more info beside the view.
 *
 * @author    Adrien Petremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import $ from 'jquery'
import _ from 'underscore'
import Backbone from 'backbone'
import BaseForm from 'pim/form'
import template from 'pim/template/grid/view-selector/line'
export default BaseForm.extend({
    template: _.template(template),
    datagridView: null,
    datagridViewType: null,
    currentViewId: null,

            /**
             * {@inheritdoc}
             */
    render: function () {
        this.$el.html(this.template({
            view: this.datagridView,
            isCurrent: (this.currentViewId === this.datagridView.id)
        }))

        this.renderExtensions()

        return this
    },

            /**
             * Set the view of this module.
             *
             * @param {Object}  view
             * @param {String}  viewType
             * @param {int}     currentViewId
             */
    setView: function (view, viewType, currentViewId) {
        this.datagridView = view
        this.datagridViewType = viewType
        this.currentViewId = currentViewId
    }
})

