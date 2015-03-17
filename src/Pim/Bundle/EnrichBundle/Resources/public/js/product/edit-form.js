'use strict';

define(
    [
        'underscore',
        'backbone',
        'text!pim/template/product/form',
        'pim/form'
    ],
    function(
        _,
        Backbone,
        template,
        BaseForm
    ) {
        var FormView = BaseForm.extend({
            template: _.template(template),
            initialize: function () {
                this.model = new Backbone.Model();
                this.state = new Backbone.Model();

                this.listenTo(this.state, 'change', this.render);

                BaseForm.prototype.initialize.apply(this, arguments);
            },
            render: function () {
                if (!this.configured) {
                    return;
                }

                this.$el.html(
                    this.template({
                        state: this.state.toJSON()
                    })
                );

                BaseForm.prototype.render.apply(this, arguments);

                return this;
            }
        });

        return FormView;
    }
);
