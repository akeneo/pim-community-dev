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
        'text!pim/template/product/form-tabs'
    ],
    function ($, _, Backbone, BaseForm, template) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'tabbable tabs-top',
            tabs: [],
            fullPanel: false,
            events: {
                'click header ul.nav-tabs li': 'selectTab'
            },

            /**
             * {@inheritdoc}
             */
            initialize: function () {
                this.tabs = [];

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.onExtensions('tab:register',  this.registerTab.bind(this));
                this.listenTo(this.getRoot(), 'pim_enrich:form:form-tabs:change', this.setCurrentTab);

                window.addEventListener('resize', this.resize.bind(this));
                this.listenTo(this.getRoot(), 'pim_enrich:form:render:after', this.resize);

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

                var tabs = _.clone(this.tabs);
                tabs = _.filter(tabs, function (tab) {
                    return !_.isFunction(tab.isVisible) || tab.isVisible();
                });

                this.$el.html(
                    this.template({
                        tabs: tabs,
                        currentTab: this.getCurrentTab(),
                        fullPanel: this.fullPanel
                    })
                );
                this.delegateEvents();
                this.initializeDropZones();

                this.ensureDefault();
                var currentTab = this.getExtension(this.getCurrentTab());
                if (currentTab) {
                    var zone = this.getZone('container');
                    zone.appendChild(currentTab.el);
                    this.renderExtension(currentTab);
                    this.resize();
                }

                var panelsExtension = this.getExtension('panels');
                if (panelsExtension) {
                    this.getZone('panels').appendChild(panelsExtension.el);
                    this.renderExtension(panelsExtension);
                }

                return this;
            },

            /**
             * Resize the container to avoid multiple scrollbar
             */
            resize: function () {
                var currentTab = this.getExtension(this.getCurrentTab());
                if (currentTab && _.isFunction(currentTab.resize)) {
                    currentTab.resize();
                } else {
                    this.resizeContainer();
                }
            },

            /**
             * Default resize method
             */
            resizeContainer: function () {
                var container = this.$('> .form-container');
                if (container.length && this.getRoot().$el.length && container.offset()) {
                    container.css(
                        {'height': ($(window).height() - container.offset().top - 4) + 'px'}
                    );
                }
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
                var needRender = false;

                if (this.getCurrentTab() !== tab) {
                    sessionStorage.setItem('current_form_tab', tab);
                    needRender = true;
                }

                if (this.fullPanel) {
                    this.fullPanel = false;
                    needRender = true;
                }

                if (needRender) {
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
                return sessionStorage.getItem('current_form_tab');
            },

            /**
             * Is the form-tabs in full panel mode ?
             *
             * @return {Boolean}
             */
            isFullPanel: function () {
                return this.fullPanel;
            },

            /**
             * Set the form tabs in full panel or not
             *
             * @param {Boolean} fullPanel
             */
            setFullPanel: function (fullPanel) {
                if (this.fullPanel !== fullPanel) {
                    this.fullPanel = fullPanel;
                    this.render();
                }
            },

            /**
             * Ensure default value for the current tab
             */
            ensureDefault: function () {
                if (!_.isNull(sessionStorage.getItem('redirectTab')) &&
                    _.findWhere(this.tabs, {code: sessionStorage.getItem('redirectTab').substring(1)})
                ) {
                    this.setCurrentTab(sessionStorage.redirectTab.substring(1));

                    sessionStorage.removeItem('redirectTab');
                }

                var currentTabIsNotDefined = _.isNull(this.getCurrentTab());
                var currentTabDoesNotExist = !_.findWhere(this.tabs, {code: this.getCurrentTab()});
                if ((currentTabIsNotDefined || currentTabDoesNotExist) && _.first(this.tabs)) {
                    this.setCurrentTab(_.first(this.tabs).code);
                }
            }
        });
    }
);
