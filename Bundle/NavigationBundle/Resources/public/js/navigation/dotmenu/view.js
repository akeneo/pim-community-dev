/* global define */
define(['jquery', 'underscore', 'backbone', 'oro/mediator', 'oro/navigation/dotmenu/item-view'],
function($, _, Backbone, mediator, DotmenuItemView) {
    'use strict';

    /**
     * @export  oro/navigation/dotmenu/view
     * @class   oro.navigation.dotmenu.View
     * @extends Backbone.View
     */
    return Backbone.View.extend({
        options: {
            el: '.pin-menus .tabbable',
            defaultTabOptions: {
                hideOnEmpty: false
            }
        },
        tabs: {},

        templates: {
            tab: _.template($("#template-dot-menu-tab").html()),
            content: _.template($("#template-dot-menu-tab-content").html()),
            emptyMessage: _.template($("#template-dot-menu-empty-message").html())
        },

        initialize: function() {
            this.$tabsContainer = this.$('.nav-tabs');
            this.$tabsContent = this.$('.tab-content');
            this.init();
            mediator.bind(
                "hash_navigation_request:complete",
                function() {
                    this.init();
                },
                this
            );
            mediator.bind(
                "tab:changed",
                function(tabId) {
                    this.chooseActiveTab(tabId);
                },
                this
            );
            this.chooseActiveTab();
        },

        init: function() {
            this.$tabsContent.find('.menu-close').click(_.bind(this.close, this));
        },

        addTab: function(options) {
            var data = _.extend(this.options.defaultTabOptions, options);

            data.$tab = this.$('#' + data.key + '-tab');
            if (!data.$tab.length) {
                data.$tab = $(this.templates.tab(data));
                this.$tabsContainer.append(data.$tab);
            }

            data.$tabContent = this.$('#' + data.key + '-content');
            if (!data.$tabContent.length) {
                data.$tabContent = $(this.templates.content(data));
                this.$tabsContent.append(data.$tabContent);
            }

            data.$tabContentContainer = data.$tabContent.find('ul');
            this.tabs[data.key] = _.clone(data);
        },

        getTab: function(key) {
            return this.tabs[key];
        },

        addTabItem: function(tabKey, item, prepend) {
            if (this.isTabEmpty(tabKey)) {
                this.cleanup(tabKey);
            }
            var el = null;
            if (_.isElement(item)) {
                el = item;
            } else if (_.isObject(item)) {
                if (!_.isFunction(item.render)) {
                    item = new DotmenuItemView({model: item});
                }
                el = item.render().$el;
            }

            if (el) {
                if (prepend) {
                    this.getTab(tabKey).$tabContentContainer.prepend(el);
                } else {
                    this.getTab(tabKey).$tabContentContainer.append(el);
                }
            }
            /**
             * Backbone event. Fired when item is added to menu
             * @event navigation_item:added
             */
            mediator.trigger("navigation_item:added", el);
        },

        cleanup: function(tabKey) {
            this.getTab(tabKey).$tabContentContainer.empty();
        },

        checkTabContent: function(tabKey) {
            var isEmpty = this.isTabEmpty(tabKey);
            if (isEmpty) {
                this.hideTab(tabKey);
            } else {
                this.showTab(tabKey);
            }
        },

        /**
         * Checks if first tab in 3 dots menu is empty
         *
         * @return {Boolean}
         */
        isFirstTabEmpty: function() {
            var children = this.$tabsContent.children();
            return children && children.first().size() &&
                (!children.first().html().trim() ||
                !children.first().find('ul').html());
        },

        /**
         * Set default tab as active based on config class
         */
        setDefaultNonEmptyTab: function() {
            this.$('.show-if-empty a').tab('show');
        },

        /**
         * Set active dots menu tab.
         *
         * @param tabId
         */
        chooseActiveTab: function(tabId) {
            if (_.isUndefined(tabId)) {
                if (this.isFirstTabEmpty()) {
                    this.setDefaultNonEmptyTab();
                }
            } else {
                if (this.getTab(tabId).$tab.index() == 0) {
                    if (!this.isTabEmpty(tabId)) {
                        this.tabs[tabId].$tab.find('a').tab('show');
                    } else {
                        this.setDefaultNonEmptyTab();
                    }
                }
            }
        },

        isTabEmpty: function(tabKey) {
            var tab = this.getTab(tabKey);
            return !tab.$tabContentContainer.children().length || tab.$tabContentContainer.html() == this.templates.emptyMessage();
        },

        hideTab: function(tabKey) {
            var tab = this.getTab(tabKey);
            if (tab.hideOnEmpty) {
                tab.$tab.hide();
            } else {
                this.getTab(tabKey).$tabContentContainer.html(this.templates.emptyMessage());
            }
        },

        showTab: function(tabKey) {
            this.getTab(tabKey).$tab.show();
        },

        close: function() {
            this.$el.parents('.open').removeClass('open');
            return false;
        }
    });
});
