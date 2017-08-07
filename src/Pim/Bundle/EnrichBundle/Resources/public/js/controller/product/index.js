define(
    [
        'underscore',
        'jquery',
        'pim/controller/base',
        'pim/form-builder'
    ],
    function (_, $, BaseController, FormBuilder) {
        return BaseController.extend({
            /**
            * {@inheritdoc}
            */
            renderRoute(route, path) {
                return FormBuilder.build('pim-product-index').then(function (form) {
                    // return $.get(path)
                    // .then(this.renderTemplate.bind(this))
                    // .promise();
                    form.setElement(this.$el).render();
                }.bind(this));
            },
            renderTemplate: function (content) {
                if (!this.active) {
                    return;
                }

                this.$el.html(content);
            }
        });
    }
);
