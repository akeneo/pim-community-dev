'use strict';

define(
    ['pim/form', 'pim/user-context', 'oro/mediator'],
    function (BaseForm, UserContext, mediator) {
        return BaseForm.extend({
            tagName: 'span',
            className: 'product-label',
            configure: function () {
                this.listenTo(UserContext, 'change:catalogLocale', this.render);
                this.listenTo(mediator, 'product:action:post_update', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                var meta = this.getRoot().model.get('meta');

                if (meta && meta.label) {
                    this.$el.text(meta.label);
                }

                return this;
            }
        });
    }
);
