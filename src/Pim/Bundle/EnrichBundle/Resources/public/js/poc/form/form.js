'use strict';

/**
 * Poc form
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
        'text!pim/template/poc/form/form',
        'pim/form/type/field/input'
    ],
    function (
        $,
        _,
        __,
        BaseForm,
        FetcherRegistry,
        template,
        input
    ) {
        return BaseForm.extend({
            className: 'tabsection',
            template: _.template(template),
            sectionTitle: __('section_title'),
            fieldTypes: {
                input: input
            },

            initialize: function (config) {
                this.config = config.config;

                this.on('pim_enrich:poc:render', this.onRender);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {

                // generate place holders for form fields
                this.$el.html(this.template({
                    sectionTitle: this.sectionTitle
                }));
                console.log(this.getFormData());
                this.renderExtensions();

                return this.trigger('pim_enrich:poc:render')
            },

            onRender: function () {
                // create promises here for fields that need additional data (ex.select options)
                // $.when()
                // when promise ready render
                // .then
                _.each(this.config.fields, function (field) {
                    console.log(field);
                    var fieldType = this.fieldTypes[field.type];

                    var formField = new fieldType();
                    formField.name = field.name;
                    formField.value = this.getFormData()[field.name];
                    formField.error = this.getFieldError(field.name);
                    formField.fieldPrefix = this.config.fieldPrefix;
                    formField.config = field.config;
                    formField.setParent(this);
                    formField.setElement(this.$('#' + field.name)).render();
                }.bind(this));

                // .endthen
            },

            getFieldError: function (field) {
                return null;
            }
        });
    }
);
