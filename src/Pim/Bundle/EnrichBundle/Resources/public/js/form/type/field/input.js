'use strict';

/**
 * Input field type
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/fetcher-registry',
        'text!pim/template/form/type/field/input'
    ],
    function (
        $,
        _,
        __,
        BaseForm,
        FetcherRegistry,
        template
    ) {
        return BaseForm.extend({
            className: 'tabsection',
            template: _.template(template),
            requiredLabel: __('pim_enrich.form.required'),
            fieldPrefix: null,
            config: {
                isRequired: false,
                isDisabled: false,
                label: null
            },
            error: null,
            name: null,
            value: null,
            events: {
                'change input': 'updateModel'
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                console.log(this.name);
                this.$el.html(this.template({
                    label: this.config.label,
                    requiredLabel: this.requiredLabel,
                    fieldPrefix: this.fieldPrefix,
                    isRequired: this.config.isRequired,
                    isDisabled: this.config.isDisabled,
                    name: this.name,
                    value: this.value,
                    error: this.error
                }));

                this.delegateEvents();

                this.renderExtensions();
            },

            updateModel: function (event) {
                var data = this.getFormData();
                console.log(event.target);
                data[this.name] = $(event.target).val();

                this.setData(data);
            }
        });
    }
);
