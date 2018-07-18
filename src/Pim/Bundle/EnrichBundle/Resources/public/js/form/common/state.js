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
        'oro/translator',
        'backbone',
        'pim/dialog',
        'pim/form',
        'pim/template/form/state'
    ],
    function (
        $,
        _,
        __,
        Backbone,
        Dialog,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            className: 'updated-status',
            template: _.template(template),
            state: null,
            linkSelector: 'a[href^="/"]:not(".no-hash")',

            /**
             * {@inheritdoc}
             */
            initialize: function (meta) {
                this.config = _.extend({}, {
                    confirmationMessage: 'pim_enrich.entity.fallback.module.edit.discard_changes',
                    confirmationTitle: 'pim_enrich.entity.fallback.module.edit.leave',
                    message: 'pim_common.entity_updated'
                }, meta.config);

                this.confirmationMessage = __(this.config.confirmationMessage, {entity: __(this.config.entity)});
                this.confirmationTitle   = __(this.config.confirmationTitle);
                this.message             = __(this.config.message);

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * @inheritdoc
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:update_state', this.render);
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.render);
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', this.collectAndRender);
                this.listenTo(this.getRoot(), 'pim_enrich:form:state:confirm', this.onConfirmation);
                this.listenTo(this.getRoot(), 'pim_enrich:form:can-leave', this.linkClicked);
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
                if (this.hasModelChanged()) {
                    event.canLeave = confirm(this.confirmationMessage);
                }
            },

            /**
             * Check if current form model has changed compared to the stored model state
             *
             * @return {boolean}
             */
            hasModelChanged: function () {
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
