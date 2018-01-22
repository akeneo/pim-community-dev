'use strict';

/**
 * Base extension for tab
 * This represents a main tab of the application, associated with icon, text and column.
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
        'routing',
        'pim/template/menu/tab',
        'oro/mediator'
    ],
    function (
        _,
        __,
        BaseForm,
        router,
        Routing,
        template,
        mediator
    ) {
        return BaseForm.extend({
            template: _.template(template),
            events: {
                'click': 'redirect'
            },
            active: false,
            items: [],
            className: 'AknHeader-menuItemContainer',

            /**
             * {@inheritdoc}
             */
            initialize: function (config) {
                this.config = config.config;
                this.items = [];

                mediator.on('pim_menu:highlight:tab', this.highlight, this);
                mediator.on('pim_menu:redirect:tab', this.redirect, this);

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_menu:register_item', this.registerItem);

                BaseForm.prototype.configure.apply(this, arguments);
            },


            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.empty();

                if (!this.config.to && !this.hasChildren()) {
                    return this;
                }

                this.$el.append(this.template({
                    active: this.active,
                    title: this.getLabel(),
                    url: Routing.generateHash(this.getRoute(), this.getRouteParams()),
                    iconModifier: this.config.iconModifier
                }));

                return BaseForm.prototype.render.apply(this, arguments);
            },

            /**
             * Redirect the user to the config destination
             *
             * @param {Event} event
             */
            redirect: function (event) {
                if (!_.has(event, 'extension')) {
                    event.stopPropagation();
                    event.preventDefault();
                }

                if (!(event.metaKey || event.ctrlKey) &&
                    ((!_.has(event, 'extension') || event.extension === this.code) && undefined !== this.getRoute())
                ) {
                    router.redirectToRoute(this.getRoute(), this.getRouteParams());
                }
            },

            /**
             * Returns the route of the tab.
             *
             * There is 2 cases here:
             * - The configuration contains a `to` element, so we did a simple redirect to this route.
             * - There is no configuration, so we need to get the first available element of the associated column.
             *   For this, we simply register all the items of the column, sort them by priority then take the first
             *   one.
             *
             * @returns {string|undefined}
             */
            getRoute: function () {
                if (undefined !== this.config.to) {
                    return this.config.to;
                } else {
                    return _.first(_.sortBy(this.items, 'position')).route;
                }
            },

            /**
             * Returns the route parameters.
             *
             * @returns {json}
             */
            getRouteParams: function () {
                if (undefined !== this.config.to) {
                    return this.config.routeParams !== 'undefined' ? this.config.routeParams : {};
                } else {
                    return _.first(_.sortBy(this.items, 'position')).routeParams;
                }
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
             * Highlight or un-highlight tab
             *
             * @param {Event} event
             * @param {string} event.extension The extension code to highlight
             */
            highlight: function (event) {
                this.active = (event.extension === this.code);

                this.render();
            },

            /**
             * Registers a new item attached to this tab.
             *
             * @param {Event}  event
             * @param {string} event.route
             * @param {number} event.position
             */
            registerItem: function (event) {
                if (event.target === this.code) {
                    this.items.push(event);
                }
            },

            /**
             * Does this tab have children elements
             *
             * @return {Boolean}
             */
            hasChildren: function () {
                return 0 < this.items.length;
            }
        });
    });
