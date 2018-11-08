'use strict';

/**
 * Create button
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
        'pim/template/form/index/create-button',
        'routing',
        'pim/dialogform',
        'pim/form-builder'
    ],
    function (
        $,
        _,
        __,
        BaseForm,
        template,
        Routing,
        DialogForm,
        FormBuilder
    ) {
        return BaseForm.extend({
            template: _.template(template),
            dialog: null,

            /**
             * {@inheritdoc}
             */
            initialize: function (config) {
                this.config = config.config;

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    title: __(this.config.title),
                    iconName: this.config.iconName,
                    url: this.config.url ? Routing.generate(this.config.url) : ''
                }));

                if (this.config.modalForm) {
                    this.$el.on('click', function () {
                        FormBuilder.build(this.config.modalForm)
                            .then(function (modal) {
                                modal.open();
                            })
                    }.bind(this));

                    return this;
                }

                // TODO-Remove the following line when all entities will be managed (TIP-730 completed)
                this.dialog = new DialogForm('#create-button-extension');

                return this;
            }
        });
    });
