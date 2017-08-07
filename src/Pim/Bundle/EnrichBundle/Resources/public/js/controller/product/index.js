define(
    [
        'pim/controller/base',
        'pim/form-builder'
    ],
    function (BaseController, FormBuilder) {
        return BaseController.extend({

            /**
            * {@inheritdoc}
            */
            renderRoute() {
                console.log('Render the parent product index');

                return FormBuilder.build('pim-product-index')
                    .then(function (form) {
                        form.setElement(this.$el).render();
                    }.bind(this));
            }
        });
    }
);
