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
        'underscore',
        'backbone',
        'pim/form',
        'text!pim/template/product/form-tabs',
        'oro/mediator'
    ],
    function (_, Backbone, BaseForm, template, mediator) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'tabbable tabs-top',
            events: {
                'click header ul.nav-tabs li': 'selectTab'
            },
            initialize: function () {
                this.state = new Backbone.Model();
                this.listenTo(this.state, 'change', this.render);

                BaseForm.prototype.initialize.apply(this, arguments);
            },
            configure: function () {
                this.onExtensions('tab:register',  _.bind(this.registerTab, this));
                this.listenTo(mediator, 'pim_enrich:form:form-tabs:change', this.setCurrentTab);

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            registerTab: function (event) {
                var tabs = this.state.get('tabs') || [];
                tabs.push({
                    code: event.code,
                    isVisible: event.isVisible,
                    label: event.label
                });

                this.state.set('tabs', tabs, {silent: true});

                if (this.state.get('currentTab') === undefined) {
                    this.state.set('currentTab', tabs[0].code, {silent: true});
                }

                this.state.trigger('change');
            },
            render: function () {
                if (!this.configured) {
                    return this;
                }

                var state = this.state.toJSON();
                state.tabs = _.filter(state.tabs, function (tab) {
                    return !_.isFunction(tab.isVisible) || tab.isVisible();
                });

                this.$el.html(
                    this.template({
                        state: state
                    })
                );
                this.delegateEvents();
                this.initializeDropZones();

                var currentTab = this.getExtension(this.state.get('currentTab'));
                if (currentTab) {
                    this.renderExtension(currentTab);
                    var zone = this.getZone('container');
                    zone.appendChild(currentTab.el);
                }

                var panelsExtension = this.getExtension('panels');
                if (panelsExtension) {
                    this.renderExtension(panelsExtension);
                    this.getZone('panels').appendChild(panelsExtension.el);
                }

                return this;
            },
            selectTab: function (event) {
                this.setCurrentTab(event.currentTarget.dataset.tab);
            },
            setCurrentTab: function (tab) {
                var needRender = false;

                if (this.state.get('currentTab') !== tab) {
                    this.state.set('currentTab', tab, {silent: true});
                    needRender = true;
                }

                if (this.state.get('fullPanel')) {
                    this.state.set('fullPanel', false, {silent: true});
                    needRender = true;
                }

                if (needRender) {
                    this.state.trigger('change');
                }

                return this;
            },
            getCurrentTab: function () {
                return this.state.get('currentTab');
            }
        });
    }
);
