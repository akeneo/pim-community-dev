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
        'text!pim/template/product/panel/container'
    ],
    function ($, _, Backbone, BaseForm, template) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'panel-container closed',
            events: {
                'click > header > .close': 'closePanel'
            },
            initialize: function () {
                this.state = new Backbone.Model();

                this.listenTo(this.state, 'change', this.render);
                window.addEventListener('resize', _.bind(this.resize, this));

                BaseForm.prototype.initialize.apply(this, arguments);
            },
            configure: function () {
                _.each(this.extensions, _.bind(function (extension) {
                    extension.on('panel:register', _.bind(this.registerPanel, this));
                }, this));

                this.listenTo(this.getParent().state, 'change:fullPanel', this.render);

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

                this.$el.html(
                    this.template({
                        state: this.state.toJSON()
                    })
                );

                if (this.state.get('currentPanel')) {
                    var currentPanel = this.extensions[this.state.get('currentPanel')];

                    this.renderExtension(currentPanel);
                }

                var selectorExtension = this.extensions.selector;
                console.log(this.code, 'triggered the rendering of', selectorExtension.code);
                this.getParent().$('>header').append(selectorExtension.render().$el);

                this.resize();

                return this;
            },
            closePanel: function () {
                this.state.set('currentPanel', null);
                this.closeFullPanel();
            },
            openFullPanel: function () {
                this.getParent().state.set('fullPanel', true);
            },
            closeFullPanel: function () {
                if (this.getParent().state.get('fullPanel')) {
                    this.getParent().state.set('fullPanel', false);
                }
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
