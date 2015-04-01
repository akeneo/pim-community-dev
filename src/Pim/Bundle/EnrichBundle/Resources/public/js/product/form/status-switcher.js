'use strict';

define(
    [
        'underscore',
        'oro/mediator',
        'pim/form',
        'text!pim/template/product/status-switcher'
    ],
    function(
        _,
        mediator,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            className: 'btn-group status-switcher',
            template: _.template(template),
            events: {
                'click li a': 'updateStatus',
            },
            render: function () {
                var status = this.getRoot().getData().enabled;

                this.$el.html(this.template({
                    status: status
                }));
                this.$el.addClass(status ? 'enabled' : 'disabled');
                this.$el.removeClass(status ? 'disabled' : 'enabled');
                this.delegateEvents();

                return this;
            },
            updateStatus: function(event) {
                var newStatus = event.currentTarget.dataset.status === 'enable';
                this.getRoot().model.set('enabled', newStatus);
                this.render();
            }
        });
    }
);
