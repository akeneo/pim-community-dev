'use strict';

/**
 * Module of the datagrid View Selector to display, if needed, a dropdown to pick
 * the view type to display in the view results.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'backbone',
        'pim/form',
        'text!pim/template/grid/view-selector/type-switcher'
    ],
    function (
        $,
        _,
        __,
        Backbone,
        BaseForm,
        template
    ) {
        return Backbone.View.extend({
            template: _.template(template),
            viewTypes: [],
            currentViewType: '',
            events: {
                'click .view-type-item': 'switchViewType'
            },

            /**
             * {@inheritdoc}
             */
            initialize: function (viewTypes) {
                this.viewTypes = viewTypes;
                this.listenTo(this, 'grid:view-selector:view-type-switched', this.onViewTypeSelected);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    __: __,
                    currentViewType: this.currentViewType,
                    viewTypes: this.viewTypes
                }));

                this.$('.view-selector-type-switcher').dropdown();

                return this;
            },

            /**
             * Method called on click on a view type in the dropdown of this template.
             * It simply triggers an event with the selected view type.
             *
             * @param {Event} event
             */
            switchViewType: function (event) {
                var viewType = $(event.target).data('value');
                this.$('.view-selector-type-switcher').dropdown('toggle');

                this.trigger('grid:view-selector:view-type-switching', viewType);
            },

            /**
             * Method called when a new view type has been selected.
             *
             * @param {string} viewType
             */
            onViewTypeSelected: function (viewType) {
                this.currentViewType = viewType;
                this.render();
            }
        });
    }
);
