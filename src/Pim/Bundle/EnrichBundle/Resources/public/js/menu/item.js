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
            template: _.template(template),
            events: {
                'click .navigation-item': 'redirect'
            },
            active: false,

            /**
             * {@inheritdoc}
             */
            initialize: function (config) {
                this.config = config.config;

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * On configure, this module triggers an event to register it to tabs.
             *
             * {@inheritdoc}
             */
            configure: function () {
                this.getRoot().trigger('pim_menu:register_item', {
                    target: this.getColumn().getTab(),
                    origin: this
                });

                this.getColumn().trigger('pim_menu:column:register_navigation_item', {
                    code: this.getRoute(),
                    label: this.getLabel()
                });

                BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.empty().append(this.template({
                    title: this.getLabel(),
                    active: this.active
                }));

                this.delegateEvents();

                return BaseForm.prototype.render.apply(this, arguments);
            },

            /**
             * Redirect the user to the config destination
             */
            redirect: function () {
                router.redirectToRoute(this.getRoute());
            },

            /**
             * Returns the route of the tab.
             *
             * @returns {string|undefined}
             */
            getRoute: function () {
                return this.config.to;
            },

            /**
             * Returns the displayed label of the tab
             *
             * @returns {string}
             */
            getLabel: function () {
                return __(this.config.title);
            },

            /**
             * @returns {Backbone.View}
             */
            getColumn: function () {
                return this.getParent().getColumn();
            },

            /**
             * Activate/deactivate the item
             *
             * @param {Boolean} active
             */
            setActive: function (active) {
                this.active = active;
                this.render();
            }
        });
    });
