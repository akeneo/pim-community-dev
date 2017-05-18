'use strict';

/**
 * Creation form asking for one code
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'text!pim/template/form/creation/code',
        'routing',
        'pim/dialogform',
        'oro/loading-mask'
    ],
    function (
        $,
        _,
        __,
        BaseForm,
        template,
        Routing,
        DialogForm,
        LoadingMask
    ) {
        return BaseForm.extend({
            template: _.template(template),
            dialog: null,
            events: {
                'change input': 'updateModel'
            },

            /**
             * {@inheritdoc}
             */
            initialize: function (config) {
                this.config = config.config;
                BaseForm.prototype.initialize.apply(this, arguments);
            },


            /**
             * Model update callback
             */
            updateModel: function () {
                this.getFormModel().set('code', this.$('input[name="code"]').val());
            },

            /**
             * Save the form content by posting it to backend
             *
             * @return {Promise}
             */
            save: function () {
                this.validationErrors = {};

                var loadingMask = new LoadingMask();
                this.$el.empty().append(loadingMask.render().$el.show());

                var dataPost = {};
                dataPost[this.config.entity] = this.getFormData();

                return $.post(Routing.generate(this.config.createRoute), dataPost)
                    .fail(function (response) {
                        this.validationErrors = response.responseJSON.values;
                        this.render();
                    }.bind(this))
                    .always(function () {
                        loadingMask.remove();
                    });
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.configured) {
                    return this;
                }

                if (this.getFormData().code === undefined) {
                    this.getFormModel().set('code', '');
                }

                this.$el.html(this.template({
                    label: __('pim_enrich.entity.create_popin.code'),
                    requiredLabel: __('pim_enrich.form.required'),
                    errors: this.validationErrors,
                    value: this.getFormModel().get('code')
                }));

                return this;
            }
        });
    });
