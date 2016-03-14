"use strict";

define([
        'pim/form',
        'pim/fetcher-registry',
        'text!pim/template/variant-group/tab/properties',
        'jquery.select2'
    ],
    function(
        BaseForm,
        FetcherRegistry,
        template
    ) {
        return BaseForm.extend({
            className: 'properties',
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.trigger('tab:register', {
                    code: this.code,
                    label: _.__('pim_enrich.form.variant_group.tab.properties.title')
                });

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({}));

                this.renderExtensions();
            },
        });
    }
);
