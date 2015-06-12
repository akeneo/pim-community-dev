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
        'text!pim/template/product/form-tabs'
    ],
    function (_, Backbone, BaseForm, template) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'tabbable tabs-top',
            events: {
                'click header ul.nav-tabs li': 'changeTab'
            },
            initialize: function () {
                this.state = new Backbone.Model();

                this.listenTo(this.state, 'change', this.render);

                BaseForm.prototype.initialize.apply(this, arguments);
            },
            configure: function () {
                _.each(this.extensions, _.bind(function (extension) {
                    extension.on('tab:register', _.bind(this.registerTab, this));
                }, this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            registerTab: function (event) {
                var tabs = this.state.get('tabs') || [];
                tabs.push({ code: event.code, label: event.label });

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

                this.$el.html(
                    this.template({
                        state: this.state.toJSON()
                    })
                );
                this.delegateEvents();

                var currentTab = this.extensions[this.state.get('currentTab')];
                if (currentTab) {
                    this.renderExtension(currentTab);
                }

                var panels = this.extensions.panels;
                if (panels) {
                    panels.getTargetElement().append(panels.el);
                    panels.render();
                }

                return this;
            },
            changeTab: function (event) {
                this.state.set('currentTab', event.currentTarget.dataset.tab);
                this.state.set('fullPanel', false);
            }
        });
    }
);
