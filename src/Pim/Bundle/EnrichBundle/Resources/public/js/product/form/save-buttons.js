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
        'pim/form',
        'text!pim/template/product/save-buttons'
    ],
    function ($, _, Backbone, BaseForm, template) {
        return BaseForm.extend({
            className: 'btn-group submit-form',
            template: _.template(template),
            buttonDefaults: {
                priority: 100,
                events: {}
            },
            events: {},
            initialize: function () {
                this.model = new Backbone.Model({
                    buttons: []
                });

                this.listenTo(this.model, 'change', this.render);

                BaseForm.prototype.initialize.apply(this, arguments);
            },
            render: function () {
                var buttons = this.model.get('buttons');
                this.$el.html(this.template({
                    primaryButton: _.first(buttons),
                    secondaryButtons: buttons.slice(1)
                }));
                this.delegateEvents();

                return this;
            },
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
