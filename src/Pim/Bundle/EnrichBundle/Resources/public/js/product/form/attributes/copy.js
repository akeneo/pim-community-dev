'use strict';

define(
    [
        'underscore',
        'pim/form',
        'text!pim/template/product/tab/attributes/copy'
    ],
    function(_, BaseForm, template) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'btn',
            id: 'product-copy',
            events: {
                'click': 'openCopyPanel'
            },
            configure: function () {
                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                this.$el.html(
                    this.template({
                        state: this.getRoot().state.toJSON()
                    })
                );

                this.delegateEvents();
                this.$el.appendTo(this.getParent().$('.tab-content > header'));

                return this;
            },
            openCopyPanel: function() {

                console.log(this.getParent().renderedFields);
            }
        });
    }
);
