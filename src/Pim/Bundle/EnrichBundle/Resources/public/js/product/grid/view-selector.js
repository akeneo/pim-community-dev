 define(
    [
        'underscore',
        'jquery',
        'pim/fetcher-registry',
        'pim/form-builder',
        'pim/form',
        'pim/template/product/grid/view-selector'
    ],
    function(
        _,
        $,
        FetcherRegistry,
        FormBuilder,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'pull-right',
            render() {
                this.$el.html(this.template({}));

                FetcherRegistry.initialize().done(function () {
                    FormBuilder.buildForm('pim-grid-view-selector').then(function (form) {
                        return form.configure('product-grid').then(function () {
                            form.setElement('#view-selector').render();
                        });
                    }.bind(this));
                });
            }
        });
    }
);
