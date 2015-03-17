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
            configure: function () {
                var promise = $.Deferred();

                this.state.set('locale', 'en_US');
                this.state.set('scope', 'ecommerce');
                this.state.set('currentTab', 'attributes');

                BaseForm.prototype.configure.apply(this, arguments).done(function() {
                    promise.resolve();
                });

                return promise.promise();
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

                _.each(this.extensions, function(extension) {
                    console.log(extension.parent.code, 'triggered the rendering of extension', extension.code);
                    extension.render();
                });

                return this;
            }
        });

        return FormView;
    }
);
