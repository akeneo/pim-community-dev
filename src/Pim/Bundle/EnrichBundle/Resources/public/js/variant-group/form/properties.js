'use strict';

define([
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/fetcher-registry',
        'text!pim/template/variant-group/tab/properties',
        'jquery.select2'
    ],
    function (
        _,
        __,
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
                    label: __('pim_enrich.form.variant_group.tab.properties.title')
                });

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({}));

                this.renderExtensions();
            }
        });
    }
);
