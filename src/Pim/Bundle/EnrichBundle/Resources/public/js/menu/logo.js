'use strict';

/**
 * Base extension forheadermenu
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'pim/form',
        'pim/router',
        'pim/template/menu/logo'
    ],
    function (
        _,
        BaseForm,
        router,
        template
    ) {
        return BaseForm.extend({
            className: 'AknHeader-menuItem',
            template: _.template(template),
            events: {
                'click': 'backHome'
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template());

                return BaseForm.prototype.render.apply(this, arguments);
            },

            /**
             * Redirect the user to app's home
             */
            backHome: function () {
                router.redirectToRoute('oro_default');
            },

            /**
             * The logo is never active, so did nothing.
             *
             * @param {string[]} codes
             */
            setActive: function (codes) {
            },

            /**
             * There is no attached breadcrumbs to menu, so did nothing.
             *
             * @returns {Array}
             */
            getBreadcrumbItems: function () {
                return [];
            }
        });
    });
