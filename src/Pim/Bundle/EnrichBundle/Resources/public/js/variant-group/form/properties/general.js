"use strict";

define([
        'pim/form',
        'pim/fetcher-registry',
        'text!pim/template/variant-group/tab/properties/general',
        'jquery.select2'
    ],
    function(
        BaseForm,
        FetcherRegistry,
        template
    ) {
        return BaseForm.extend({
            className: 'accordion-group',
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    model: this.getFormData()
                }));

                this.$el.find('select.select2').select2({});

                this.renderExtensions();
            },
        });
    }
);
