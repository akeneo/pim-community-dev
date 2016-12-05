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
        return BaseForm.extend({
            template: _.template(template),
            viewTypes: [],
            currentViewType: '',
            events: {
                'click [data-action="switchViewType"]': 'switchViewType'
            },

            /**
             * {@inheritdoc}
             */
            initialize: function (viewTypes) {
                this.viewTypes = viewTypes;
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (this.viewTypes.length > 1) {
                    this.$el.html(this.template({
                        __: __,
                        currentViewType: this.currentViewType,
                        viewTypes: this.viewTypes
                    }));

                    this.$('.AknActionButton').dropdown();
                    this.renderExtensions();
                }

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
                this.$('.AknActionButton').dropdown('toggle');
                this.setCurrentViewType(viewType);

                this.trigger('grid:view-selector:switch-type', viewType);
            },

            /**
             * Setter for the current view type of this module's dropdown.
             *
             * @param {string} viewType
             */
            setCurrentViewType: function (viewType) {
                this.currentViewType = viewType;
                this.render();
            }
        });
    }
);
