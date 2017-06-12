'use strict';

/**
 * Common add select footer view
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'backbone',
        'pim/template/form/add-select/footer'
    ],
    function (
        $,
        _,
        Backbone,
        template
    ) {
        return Backbone.View.extend({
            template: _.template(template),
            buttonTitle: null,
            numberOfItems: 0,
            countTitle: null,
            addEvent: null,

            events: {
                'click button': 'onAdd'
            },

            /**
             * {@inheritdoc}
             */
            initialize: function () {
                this.buttonTitle   = this.options.buttonTitle;
                this.countTitle    = this.options.countTitle;
                this.addEvent      = this.options.addEvent;
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    buttonTitle: this.buttonTitle,
                    numberOfItems: this.numberOfItems,
                    countTitle: this.countTitle
                }));

                return this;
            },

            /**
             * Update the item counter line and re-render the view.
             *
             * @param {int|string} number
             */
            updateNumberOfItems: function (number) {
                this.numberOfItems = number;

                this.render();
            },

            /**
             * Method called when the 'add' button is clicked
             */
            onAdd: function () {
                this.trigger(this.addEvent);
            }
        });
    }
);
