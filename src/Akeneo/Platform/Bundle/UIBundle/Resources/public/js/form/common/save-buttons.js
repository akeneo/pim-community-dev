'use strict';
/**
 * Save buttons extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'backbone',
        'oro/mediator',
        'pim/form',
        'pim/template/form/save-buttons'
    ],
    function ($, _, Backbone, mediator, BaseForm, template) {
        return BaseForm.extend({
            className: 'AknTitleContainer-rightButton',
            template: _.template(template),
            buttonDefaults: {
                priority: 100,
                events: {}
            },
            events: {},

            /**
             * {@inheritdoc}
             */
            initialize: function () {
                this.model = new Backbone.Model({
                    buttons: []
                });
                this.events = {};

                this.on('save-buttons:add-button', this.addButton.bind(this));

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                var buttons = this.model.get('buttons');
                if (buttons.length > 0) {
                    this.$el.html(this.template({
                        primaryButton: _.first(buttons),
                        secondaryButtons: buttons.slice(1)
                    }));
                }
                this.delegateEvents();

                return this;
            },

            /**
             * Add a button to the main button
             *
             * @param {Object} options
             */
            addButton: function (options) {
                var button = _.extend({}, this.buttonDefaults, options);
                this.events = _.extend(this.events, button.events);
                var buttons = this.model.get('buttons');

                buttons.push(button);
                buttons = _.sortBy(buttons, 'priority').reverse();
                this.model.set('buttons', buttons);
            }
        });
    }
);
