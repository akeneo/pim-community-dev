'use strict';

/**
 * Extension for menu columns
 * This extends the default column and adds some behaviors only used in the menu context (visibility)
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'pim/form/common/column',
        'pim/router',
        'oro/mediator'
    ],
    function (
        _,
        Column,
        router,
        mediator
    ) {
        return Column.extend({
            active: false,

            /**
             * {@inheritdoc}
             */
            initialize: function () {
                mediator.on('pim_menu:highlight:tab', this.highlight, this);

                Column.prototype.initialize.apply(this, arguments);
            },

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
             * Highlight or un-highlight tab
             *
             * @param {Event} event
             * @param {string} event.extension The extension code to highlight
             */
            highlight: function (event) {
                this.active = (event.extension === this.getTab());

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
             * The DOM element contains a `data-tab` attribute for compatibility with tab Bootstram tabs.
             *
             * {@inheritdoc}
             */
            redirect: function (event) {
                router.redirectToRoute(event.currentTarget.dataset.tab);
            },

            /**
             * Registers a new item to display on navigation template
             *
             * @param {Event}    navigationItem
             * @param {string}   navigationItem.label
             * @param {function} navigationItem.isVisible
             * @param {string}   navigationItem.route
             * @param {number}   navigationItem.position
             */
            registerNavigationItem: function (navigationItem) {
                Column.prototype.registerNavigationItem.apply(this, arguments);

                this.getRoot().trigger('pim_menu:register_item', {
                    target: this.getTab(),
                    route: navigationItem.route,
                    position: navigationItem.position,
                    routeParams: navigationItem.routeParams
                });
            }
        });
    });
