'use strict';
/**
 * Display a vertical column for navigation or filters
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/template/form/column',
        'pim/template/form/column-navigation'
    ],
    function (
        _,
        __,
        BaseForm,
        template,
        navigationTemplate
    ) {
        return BaseForm.extend({
            className: 'AknColumn',
            template: _.template(template),
            navigationTemplate: _.template(navigationTemplate),
            events: {
                'click .AknColumn-collapseButton': 'toggleColumn',
                'click .navigation-link': 'redirect'
            },
            navigationItems: [],

            /**
             * @param {string} meta.config.navigationTitle Title of the navigation dropdown
             * @param {string} meta.config.stateCode       This is a key to identify each module using column, to
             *                 store if the column is collapsed or not. If you want to use the different collapsed
             *                 states, use different "stateCode" value.
             *
             * {@inheritdoc}
             */
            initialize: function (meta) {
                this.config = meta.config;
                this.navigationItems = [];

                return BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.onExtensions('pim_menu:column:register_navigation_item', this.registerNavigationItem);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.empty().append(this.template());

                if (!_.isEmpty(this.getNavigationItems())) {
                    this.$el.find('.column-inner').prepend(this.navigationTemplate({
                        navigationItems: this.getNavigationItems(),
                        title: __(this.config.navigationTitle)
                    }));
                }

                if (this.isCollapsed()) {
                    this.setCollapsed(true);
                }

                this.delegateEvents();

                return BaseForm.prototype.render.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            toggleColumn: function () {
                this.setCollapsed(!this.isCollapsed());
            },

            /**
             * Returns true if the column is collapsed.
             * It uses the session storage with a key attached to this module.
             * If no key was found, returns false by default.
             *
             * @returns {boolean}
             */
            isCollapsed: function () {
                var result = sessionStorage.getItem(this.getSessionStorageKey());

                if (null === result) {
                    return false;
                }

                return '1' === result;
            },

            /**
             * Stores in the session storage if the column is collapsed or not.
             *
             * @param {boolean} value
             */
            setCollapsed: function (value) {
                sessionStorage.setItem(this.getSessionStorageKey(), value ? '1' : '0');

                var collapseModifier = '';
                if (this.config.collapsedModifier !== undefined) {
                    collapseModifier = this.config.collapsedModifier;
                }
                if (value) {
                    this.$el.addClass('AknColumn--collapsed ' + collapseModifier);
                } else {
                    this.$el.removeClass('AknColumn--collapsed ' + collapseModifier);
                }
            },

            /**
             * Returns the key used by the session storage for this module.
             *
             * @returns {string}
             */
            getSessionStorageKey: function () {
                return 'collapsedColumn_' + this.config.stateCode;
            },

            /**
             * Registers a new item to display on navigation template
             *
             * @param {Event}    navigationItem
             * @param {string}   navigationItem.label
             * @param {function} navigationItem.isVisible
             * @param {string}   navigationItem.code
             */
            registerNavigationItem: function (navigationItem) {
                this.navigationItems.push(navigationItem);
            },

            /**
             * Returns the visible navigation items
             *
             * @returns {Array}
             */
            getNavigationItems: function () {
                return _.filter(this.navigationItems, function (navigationItem) {
                    return !_.isFunction(navigationItem.isVisible) || navigationItem.isVisible();
                });
            },

            /**
             * @param {Event} event
             */
            redirect: function (event) {
                this.getRoot().trigger('column-tab:select-tab', event);
            }
        });
    }
);
