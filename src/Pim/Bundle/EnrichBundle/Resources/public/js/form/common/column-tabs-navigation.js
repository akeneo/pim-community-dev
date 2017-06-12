'use strict';
/**
 * Display navigation links in column for the tab display
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/template/form/column-tabs-navigation'
    ],
    function (
        $,
        _,
        __,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            tabs: [],
            template: _.template(template),
            currentTab: null,
            events: {
                'click .column-navigation-link': 'selectTab'
            },
            currentKey: 'current_column_tab',

            /**
             * @param {string} meta.config.title Translation key of the block title
             *
             * {@inheritdoc}
             */
            initialize: function (meta) {
                this.config = meta.config;

                return BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.tabs = [];
                
                this.currentTab = sessionStorage.getItem(this.currentKey);

                this.listenTo(this.getRoot(), 'column-tab:register', this.registerTab);
                this.listenTo(this.getRoot(), 'column-tab:select-tab', this.setCurrentTab);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el
                    .empty()
                    .html(this.template({
                        tabs: this.getTabs(),
                        currentTab: this.getCurrentTabOrDefault(),
                        title: __(this.config.title)
                    }));
            },

            /**
             * Registers a new tab
             *
             * @param event
             */
            registerTab: function (event) {
                var tab = {
                    code: event.code,
                    isVisible: event.isVisible,
                    label: event.label
                };
                this.tabs.push(tab);
                this.getColumn().trigger('pim_menu:column:register_navigation_item', tab);

                this.render();
            },

            /**
             * Displays another tab
             *
             * @param event
             */
            selectTab: function (event) {
                this.getRoot().trigger('column-tab:select-tab', event);
                this.setCurrentTab(event.currentTarget.dataset.tab);
            },

            /**
             * Set the current tab
             *
             * @param {string} tabCode
             */
            setCurrentTab: function (tabCode) {
                this.currentTab = tabCode;
                this.render();
            },

            /**
             * Returns the current tab.
             * If there is no selected tab, returns the first available tab.
             */
            getCurrentTabOrDefault: function () {
                var result = _.findWhere(this.getTabs(), {code: this.currentTab});

                return (undefined !== result) ? result.code : _.first(_.pluck(this.tabs, 'code'));
            },

            /**
             * Returns the list of visible tabs
             */
            getTabs: function () {
                return _.filter(this.tabs, function (tab) {
                    return !_.isFunction(tab.isVisible) || tab.isVisible();
                });
            },

            /**
             * @returns {Backbone.View}
             */
            getColumn: function () {
                return this.getParent().getColumn();
            }
        });
    }
);
