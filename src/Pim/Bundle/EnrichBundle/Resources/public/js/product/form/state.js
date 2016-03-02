'use strict';
/**
 * State manager extension
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
        'pim/dialog',
        'pim/form',
        'text!pim/template/product/state',
        'oro/navigation',
        'oro/mediator'
    ],
    function (
        $,
        _,
        Backbone,
        Dialog,
        BaseForm,
        template,
        Navigation,
        mediator
    ) {
        return BaseForm.extend({
            className: 'updated-status',
            template: _.template(template),
            state: null,
            linkSelector: 'a[href^="/"]:not(".no-hash")',
            message: _.__('pim_enrich.info.entity.updated'),
            confirmationMessage: _.__(
                'pim_enrich.confirmation.discard_changes',
                {
                    'entity': _.__('pim_enrich.entity.product.title')
                }
            ),
            confirmationTitle: _.__('pim_enrich.confirmation.leave'),

            /**
             * @inheritdoc
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:update_state', this.render);
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.render);
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', this.collectAndRender);
                this.listenTo(this.getRoot(), 'pim_enrich:form:state:confirm', this.onConfirmation);
                mediator.on('hash_navigation_click', this.linkClicked.bind(this), 'pim_enrich:form');
                $(window).on('beforeunload', this.beforeUnload.bind(this));

                Backbone.Router.prototype.on('route', this.unbindEvents);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * Detach event listeners
             */
            unbindEvents: function () {
                $(window).off('beforeunload', this.beforeUnload);
            },

            /**
             * @inheritdoc
             */
            render: function () {
                if (null === this.state || undefined === this.state) {
                    this.collectState();
                }

                this.$el.html(
                    this.template({
                        message: this.message
                    })
                ).css('opacity', this.hasModelChanged() ? 1 : 0);

                return this;
            },

            /**
             * Store a stringified representation of the form model for further comparisons
             */
            collectState: function () {
                this.state = JSON.stringify(this.getFormData());
            },

            /**
             * Force collect state and re-render
             */
            collectAndRender: function () {
                this.collectState();
                this.render();
            },

            /**
             * Callback triggered on beforeunload event
             */
            beforeUnload: function () {
                if (this.hasModelChanged()) {
                    return this.confirmationMessage;
                }
            },

            /**
             * Callback triggered on any link click event to ask confirmation if there are unsaved changes
             *
             * @param {Object} event
             *
             * @return {boolean}
             */
            linkClicked: function (event) {
                event.stoppedProcess = true;

                var doAction = function () {
                    Navigation.getInstance().setLocation(event.link);
                };

                this.confirmAction(this.confirmationMessage, this.confirmationTitle, doAction);

                return false;
            },

            /**
             * Check if current form model has changed compared to the stored model state
             *
             * @return {boolean}
             */
            hasModelChanged: function () {
                if (this.state !== JSON.stringify(this.getFormData())) {
                    /*global console: true */
                    console.log(this.state);
                    console.log(JSON.stringify(this.getFormData()));
                }

                return this.state !== JSON.stringify(this.getFormData());
            },

            /**
             * Display a dialog modal to ask an action confirmation if model has changed
             *
             * @param {string} message
             * @param {string} title
             * @param {function} action
             */
            confirmAction: function (message, title, action) {
                if (this.hasModelChanged()) {
                    Dialog.confirm(message, title, action);
                } else {
                    action();
                }
            },

            /**
             * Callback that can be triggered from anywhere to ask an action confirmation
             *
             * @param {Object} event
             */
            onConfirmation: function (event) {
                this.confirmAction(
                    event.message || this.confirmationMessage,
                    event.title || this.confirmationTitle,
                    event.action
                );
            }
        });
    }
);
