'use strict';

/**
 * Index extension for any basic screen with grid
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
        'pim/template/form/index/index',
        'pim/form-builder'
    ],
    function (
        $,
        _,
        __,
        BaseForm,
        template,
        formBuilder
    ) {
        return BaseForm.extend({
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            initialize: function (config) {
                this.config = config.config || {};

                if (_.has(config, 'forwarded-events')) {
                    this.forwardMediatorEvents(config['forwarded-events']);
                }

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    title: __(this.config.title)
                }));

                this.renderExtensions();

                formBuilder.buildForm('pim-menu-user-navigation').then(function (form) {
                    $('.user-menu').append(form.el);
                    form.render();
                }.bind(this));

                return this;
            }
        });
    });
