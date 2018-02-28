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
            className: 'AknCatalogVolume-hint AknCatalogVolume-hint--header',
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

                this.$el.html(this.template({
                    title: __(this.config.title)
                    .replace('{{values}}', product_values.value)
                    .replace('{{average}}', product_values_average.value),
                    description: __(this.config.description)
                }));
            }
        });
    }
);
