'use strict';
/**
 * Form tabs extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'backbone',
        'pim/form',
        'pim/template/form/form-tabs'
    ],
    function ($, _, Backbone, BaseForm, template) {
        return BaseForm.extend({
            template: _.template(template),

            className: 'tabbable tabs-top',

            tabs: [],

            urlParsed: false,

            events: {
                'click header ul.nav-tabs li': 'selectTab'
            },

            currentKey: 'current_form_tab',

            config: {},

            /**
             * {@inheritdoc}
             */
            initialize: function (meta) {
                this.config = _.extend({}, {
                    centered: false
                }, meta.config);
                this.tabs = [];

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.onExtensions('tab:register', this.registerTab.bind(this));
                this.listenTo(this.getRoot(), 'pim_enrich:form:form-tabs:change', this.setCurrentTab);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * Register a tab into the form tab extension
             *
             * @param {Event} event
             */
            registerTab: function (event) {
                this.tabs.push({
                    code: event.code,
                    isVisible: event.isVisible,
                    label: event.label
                });

                this.render();
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.configured || _.isEmpty(this.tabs)) {
                    return this;
                }

                var tabs = this.getTabs();
                this.ensureDefault();

                this.$el.html(
                    this.template({
                        tabs: tabs,
                        currentTab: this.getCurrentTab(),
                        centered: this.config.centered
                    })
                );
                this.delegateEvents();
                this.initializeDropZones();

                var currentTab = this.getTabExtension(this.getCurrentTab());
                if (currentTab) {
                    var zone = this.getZone('container');
                    zone.appendChild(currentTab.el);
                    this.renderExtension(currentTab);
                }

                var panelsExtension = this.getExtension('panels');
                if (panelsExtension) {
                    this.getZone('panels').appendChild(panelsExtension.el);
                    this.renderExtension(panelsExtension);
                }

                return this;
            },

            /**
             * Get visible tabs
             *
             * @return {Array}
             */
            getTabs: function () {
                var tabs = _.clone(this.tabs);
                tabs = _.filter(tabs, function (tab) {
                    return !_.isFunction(tab.isVisible) || tab.isVisible();
                });

                return tabs;
            },

            /**
             * Select a tab in the form-tabs
             *
             * @param {Event} event
             */
            selectTab: function (event) {
                this.setCurrentTab(event.currentTarget.dataset.tab);
            },

            /**
             * Set the current tab and ask render if needed
             *
             * @param {string} tab
             */
            setCurrentTab: function (tab) {
                if (this.getCurrentTab() !== tab) {
                    sessionStorage.setItem(this.currentKey, tab);
                    this.render();
                }

                return this;
            },

            /**
             * get the current tab
             *
             * @return {string}
             */
            getCurrentTab: function () {
                return sessionStorage.getItem(this.currentKey);
            },

            /**
             * Ensure default value for the current tab
             */
            ensureDefault: function () {
                var tabs = this.getTabs();

                if (!_.isNull(sessionStorage.getItem('redirectTab')) &&
                    _.findWhere(tabs, {code: sessionStorage.getItem('redirectTab').substring(1)})
                ) {
                    this.setCurrentTab(sessionStorage.redirectTab.substring(1));

                    sessionStorage.removeItem('redirectTab');
                }

                var currentTabIsNotDefined = _.isNull(this.getCurrentTab());
                var currentTabDoesNotExist = !_.findWhere(tabs, {code: this.getCurrentTab()});
                if ((currentTabIsNotDefined || currentTabDoesNotExist) && _.first(tabs)) {
                    this.setCurrentTab(_.first(tabs).code);
                }
            },

            /**
             * Get a child tab extension
             *
             * @param {string} code
             *
             * @return {Object}
             */
            getTabExtension: function (code) {
                return this.extensions[_.find(this.extensions, function (extension) {
                    var extensionCode = extension.config && extension.config.tabCode ?
                        extension.config.tabCode :
                        extension.code;
                    var expectedPosition = extensionCode.length - code.length;

                    return expectedPosition >= 0 && expectedPosition === extensionCode.indexOf(code, expectedPosition);
                }).code];
            }
        });
    }
);
