'use strict';

/**
 * Add attribute line view.
 * This view is used to display an attribute line in the add-attribute select2 module.
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
        'text!pim/template/attribute/add-attribute-line'
    ],
    function (
        $,
        _,
        Backbone,
        template
    ) {
        return Backbone.View.extend({
            template: _.template(template),
            checked: false,
            attributeItem: null,

            /**
             * {@inheritdoc}
             */
            initialize: function () {
                this.attributeItem = this.options.attributeItem;
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    item:    this.attributeItem,
                    checked: this.checked
                }));

                return this;
            },

            /**
             * Update the checkbox status then render the view
             *
             * @param {bool} checked
             */
            setCheckedCheckbox: function (checked) {
                this.checked = checked;

                this.render();
            }
        });
    }
);
