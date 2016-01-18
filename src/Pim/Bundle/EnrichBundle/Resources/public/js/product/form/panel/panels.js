'use strict';
/**
 * Panel manager extension
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
        'oro/mediator',
        'text!pim/template/product/panel/container'
    ],
    function ($, _, Backbone, BaseForm, mediator, template) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'panel-container closed',
            events: {
                'click > header > .close': 'closePanel'
            },
            initialize: function () {
                this.state = new Backbone.Model();

                this.listenTo(this.state, 'change', this.render);
                window.addEventListener('resize', this.resize.bind(this));

                BaseForm.prototype.initialize.apply(this, arguments);
            },
            configure: function () {
                this.onExtensions('panel:register', this.registerPanel.bind(this));
                this.listenTo(this.getRoot(), 'pim_enrich:form:render:after', this.resize);

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            registerPanel: function (event) {
                var panels = this.state.get('panels') || [];
                panels.push({ code: event.code, label: event.label });

                this.state.set('panels', panels);
            },
            render: function () {
                if (!this.configured) {
                    return this;
                }

                this.$el[this.state.get('currentPanel') ? 'removeClass' : 'addClass']('closed');

                var currentPanel = _.findWhere(this.state.get('panels'), {code: this.state.get('currentPanel')});
                this.$el.html(
                    this.template({
                        label: currentPanel ? currentPanel.label : this.state.get('currentPanel')
                    })
                );
                this.initializeDropZones();

                if (this.state.get('currentPanel')) {
                    currentPanel = this.getExtension(this.state.get('currentPanel'));
                    this.renderExtension(currentPanel);
                    this.getZone('panel-content').appendChild(currentPanel.el);
                }

                var selectorExtension = this.getExtension('selector');
                this.renderExtension(selectorExtension);
                this.getParent().$('>header').append(selectorExtension.$el);

                this.delegateEvents();
                this.resize();

                return this;
            },
            closePanel: function () {
                this.state.set('currentPanel', null);
                this.closeFullPanel();
            },
            openFullPanel: function () {
                this.getParent().setFullPanel(true);
            },
            closeFullPanel: function () {
                this.getParent().setFullPanel(false);
            },
            resize: function () {
                var panelContent = this.$('.panel-content');
                if (panelContent.length) {
                    panelContent.css(
                        {'height': ($(window).height() - panelContent.offset().top - 4) + 'px'}
                    );
                }
            }
        });
    }
);
