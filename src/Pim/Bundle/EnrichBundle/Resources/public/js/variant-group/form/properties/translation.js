"use strict";

define([
        'pim/form',
        'pim/fetcher-registry',
        'text!pim/template/variant-group/tab/properties/translation'
    ],
    function(
        BaseForm,
        FetcherRegistry,
        template
    ) {
        return BaseForm.extend({
            className: 'accordion-group',
            template: _.template(template),
            events: {
                'change .label-field': 'updateModel'
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                FetcherRegistry.getFetcher('locale').fetchAll().then(function (locales) {
                    this.$el.html(this.template({
                        model: this.getFormData(),
                        locales: locales
                    }));
                }.bind(this));

                this.renderExtensions();
            },

            updateModel: function (event) {
                var data = this.getFormData();

                data.labels[event.target.dataset.locale] = event.target.value;

                this.setData(data);
            }
        });
    }
);
