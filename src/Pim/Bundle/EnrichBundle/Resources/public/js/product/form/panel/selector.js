'use strict';

define(
    [
        'underscore',
        'pim/form',
        'text!pim/template/product/panel/selector'
    ],
    function(_, BaseForm, template) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'panel-selector btn-group',
            events: {
                'click button': 'changePanel'
            },
            render: function () {
                this.$el.html(
                    this.template({
                        state: this.getRoot().state.toJSON()
                    })
                );

                this.delegateEvents();

                return this;
            },
            changePanel: function (event) {
                this.getRoot().state.set('currentPanel', event.currentTarget.dataset.panel);

                this.getParent().render();
                this.getParent().$el.removeClass('closed');
            }
        });
    }
);
