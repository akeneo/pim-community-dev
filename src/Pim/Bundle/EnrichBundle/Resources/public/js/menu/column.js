'use strict';

/**
 * Base extension forheadermenu
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'pim/form/common/column',
        'pim/router'
    ],
    function (
        _,
        Column,
        router
    ) {
        return Column.extend({
            active: false,

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (this.active) {
                    return Column.prototype.render.apply(this, arguments);
                } else {
                    return this.$el.empty();
                }
            },

            /**
             * Activate/deactivate the column using the attached tab code
             *
             * @param {string[]} codes
             */
            setActive: function (codes) {
                this.active = _.contains(codes, this.getTab());
                this.render();
            },

            /**
             * Returns the code of the attached tab
             *
             * @returns {string}
             */
            getTab: function () {
                return this.config.tab;
            },

            /**
             * @param {Event} event
             */
            redirect: function (event) {
                router.redirectToRoute(event.currentTarget.dataset.tab);
            },

            /**
             * There is no attached breadcrumbs to column extension, so did nothing.
             *
             * @param {string[]} codes
             */
            getBreadcrumbItems: function (codes) {
                return [];
            }
        });
    });
