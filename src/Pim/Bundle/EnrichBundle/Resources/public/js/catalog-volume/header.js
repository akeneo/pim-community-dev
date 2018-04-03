'use strict';

define(
    [
        'underscore',
        'pim/form',
        'oro/translator',
        'pim/template/catalog-volume/header'
    ],
    function (
        _,
        BaseForm,
        __,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            initialize: function (options) {
                this.config = Object.assign({}, options.config);

                return BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render() {
                const {
                    product_values,
                    product_values_average
                } = this.getRoot().getFormData();

                if (undefined !== product_values && product_values > 0) {
                    this.$el.html(this.template({
                        title: __(this.config.title)
                            .replace('{{values}}', parseInt(product_values.value).toLocaleString('en', { useGrouping: true }))
                            .replace('{{average}}', product_values_average.value),
                        description: __(this.config.description)
                    }));
                }
            }
        });
    }
);
