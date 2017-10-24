'use strict';

/**
 * Confirm button extension
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/form',
        'routing',
        'pim/template/form/index/confirm-button'
    ],
    function (
        _,
        __,
        BaseForm,
        Routing,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            initialize: function (config) {
                this.config = config.config || {};

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    buttonClass: this.config.buttonClass,
                    buttonLabel: __(this.config.buttonLabel),
                    title: __(this.config.title),
                    message: __(this.config.message),
                    url: Routing.generate(this.config.url),
                    redirectUrl: Routing.generate(this.config.redirectUrl),
                    errorMessage: __(this.config.errorMessage),
                    successMessage: __(this.config.successMessage),
                    subTitle: __(this.config.subTitle)
                }));

                this.renderExtensions();

                return this;
            }
        });
    });
