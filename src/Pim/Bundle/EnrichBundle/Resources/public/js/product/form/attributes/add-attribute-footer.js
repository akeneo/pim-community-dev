'use strict';

/**
 * Add attribute footer view.
 * This view is used to display selected attributes counter and the button to add them.
 *
 * @author    Adrien Petremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'backbone',
        'text!pim/template/attribute/add-attribute-footer'
    ],
    function (
        $,
        _,
        Backbone,
        template
    ) {
        return Backbone.View.extend({
            template: _.template(template),
            buttonTitle: '[add]',

            events: {
                'click button': 'onAdd'
            },

            /**
             * {@inheritdoc}
             */
            initialize: function () {
                this.buttonTitle   = this.options.buttonTitle;
                this.onAddCallback = this.options.onAddCallback;
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    buttonTitle:        this.buttonTitle,
                    numberOfAttributes: this.numberOfAttributes
                }));

                return this;
            },

            /**
             * Update the attribute counter line and re-render the whole view.
             *
             * @param {int|string} number
             */
            updateNumberOfAttributes: function (number) {
                this.numberOfAttributes = number;

                this.render();
            },

            /**
             * Method called when the 'add' button is clicked
             */
            onAdd: function () {
                this.trigger('add-attributes');
            }
        });
    }
);
