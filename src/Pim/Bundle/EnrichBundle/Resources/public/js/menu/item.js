'use strict';

/**
 * Base extension for menu
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/router',
        'pim/template/menu/item'
    ],
    function (
        _,
        __,
        BaseForm,
        router,
        template
    ) {
        return BaseForm.extend({
            tagName: 'li',
            className: 'AknMainMenu-item',
            template: _.template(template),
            events: {
                'click .menu-link': 'redirect'
            },

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
                    hasChild: 0 < _.keys(this.extensions).length
                }));

                BaseForm.prototype.render.apply(this, arguments);
            },

            /**
             * Redirect the user to the config destination
             */
            redirect: function () {
                router.redirectToRoute(this.config.to);
            }
        });
    });
