 'use strict';

define(
    ['underscore', 'pim/form', 'text!pim/template/product/meta/family'],
    function (_, BaseForm, template) {
        return BaseForm.extend({
            tagName: 'span',
            className: 'family',
            template: _.template(template),
            configure: function () {
                this.listenTo(this.getRoot().model, 'change:family', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                if (!this.configured) {
                    return this;
                }

                this.$el.html(
                    this.template({
                        product: this.getData()
                    })
                );

                return BaseForm.prototype.render.apply(this, arguments);
            }
        });
    }
);
