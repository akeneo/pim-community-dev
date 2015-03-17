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

                //Should be given by conf
                this.state.set('locale', 'en_US');
                this.state.set('scope', 'ecommerce');

                this.listenTo(this.state, 'change', this.render);

                BaseForm.prototype.initialize.apply(this, arguments);
            },
            render: function () {
                if (!this.configured) {
                    return this;
                }

                this.$el.html(
                    this.template({
                        state: this.state.toJSON()
                    })
                );

                _.each(this.extensions, function(extension) {
                    console.log(extension.parent.code, 'triggered the rendering of', extension.code);
                    extension.render();
                });

                return this;
            }
        });

        return FormView;
    }
);
