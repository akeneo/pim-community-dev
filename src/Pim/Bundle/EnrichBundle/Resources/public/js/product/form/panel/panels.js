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
        'pim/template/product/panel/container'
    ],
    function ($, _, Backbone, BaseForm, mediator, template) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'AknPanelContainer panel-container',
            events: {
                'click > header > .close': 'closePanel'
            },

            /**
             * {@inheritdoc}
             */
            initialize: function () {
                this.panels = [];

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.onExtensions('panel:register', this.registerPanel.bind(this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * Register a panel to be displayed
             *
             * @param {Event} event
             */
            registerPanel: function (event) {
                this.panels.push({ code: event.code, label: event.label });

                this.render();
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.configured) {
                    return this;
                }

                this.$el.closest('.AknTabContainer-panels')[this.getCurrentPanelCode() ? 'removeClass' : 'addClass'](
                    'AknTabContainer-panels--closed'
                );

                var currentPanel = _.findWhere(this.panels, {code: this.getCurrentPanelCode()});
                this.$el.html(
                    this.template({
                        label: currentPanel ? currentPanel.label : this.getCurrentPanelCode()
                    })
                );
                this.initializeDropZones();

                if (this.getCurrentPanelCode()) {
                    this.renderExtension(this.getExtension(this.getCurrentPanelCode()));
                }

                var selectorExtension = this.getExtension('selector');
                selectorExtension.render();
                $(this.getParent().getZone('side-buttons')).append(selectorExtension.$el);

                this.delegateEvents();

                return this;
            },

            /**
             * Close the panel
             */
            closePanel: function () {
                this.setCurrentPanelCode(null);
                this.closeFullPanel();
            },

            /**
             * Open the full size panel
             */
            openFullPanel: function () {
                this.getParent().setFullPanel(true);
            },

            /**
             * Close the full size panel
             */
            closeFullPanel: function () {
                this.getParent().setFullPanel(false);
            },

            /**
             * Get the curent panel code
             */
            getCurrentPanelCode: function () {
                return sessionStorage.getItem('current_form_panel_' + this.getFormCode());
            },

            /**
             * Set the curent panel code
             */
            setCurrentPanelCode: function (code) {
                if (code) {
                    sessionStorage.setItem('current_form_panel_' + this.getFormCode(), code);
                } else {
                    sessionStorage.removeItem('current_form_panel_' + this.getFormCode());
                }

                this.render();
            }
        });
    }
);
