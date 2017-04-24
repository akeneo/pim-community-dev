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
        'text!pim/template/form/column-navigation'
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
                'click .column-navigation-link': 'selectTab',
                'click .AknDropdown-menuLink': 'selectTab'
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.tabs = [];

                this.listenTo(this.getRoot(), 'column-tab:register', this.registerTab);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el
                    .empty()
                    .html(this.template({
                        tabs: this.tabs,
                        currentTab: this.getCurrentTabOrDefault(),
                        title: __('pim_enrich.entity.product.navigation')
                    }));
            },

            /**
             * Registers a new tab
             *
             * @param event
             */
            registerTab: function (event) {
                this.tabs.push({
                    code: event.code,
                    isVisible: event.isVisible,
                    label: event.label
                });

                this.currentTab = event.currentTab;

                this.render();
            },

            /**
             * Displays another tab
             *
             * @param event
             */
            selectTab: function (event) {
                this.getRoot().trigger('column-tab:select-tab', event);

                this.currentTab = event.currentTarget.dataset.tab;

                this.render();
            },

            /**
             * Returns the current tab.
             * If there is no selected tab, returns the first available tab.
             */
            getCurrentTabOrDefault: function () {
                _.each(this.tabs, function (tab) {
                    if (tab.code === this.currentTab) {
                        return this.currentTab;
                    }
                }.bind(this));

                return _.first(_.pluck(this.tabs, 'code'));
            }
        });
    }
);
