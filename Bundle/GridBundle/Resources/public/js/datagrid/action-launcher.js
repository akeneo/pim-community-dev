/* global define */
define(['jquery', 'underscore', 'backbone'],
function($, _, Backbone) {
    'use strict';

    /**
     * Action launcher implemented as simple link. Click on link triggers action run
     *
     * Events:
     * click: Fired when launcher was clicked
     *
     * @export  oro/datagrid/action-launcher
     * @class   oro.datagrid.ActionLauncher
     * @extends Backbone.View
     */
    return Backbone.View.extend({
        /** @property */
        enabled: true,

        /** @property {String} */
        tagName: 'a',

        /** @property {Boolean} */
        onClickReturnValue: true,

        /** @property {oro.datagrid.AbstractAction} */
        action: undefined,

        /** @property {String} */
        label: undefined,

        /** @property {String} */
        icon: undefined,

        /** @property {String} */
        iconClassName: undefined,

        /** @property {String} */
        className: undefined,

        /** @property {String} */
        link: 'javascript:void(0);',

        /** @property {String} */
        runAction: true,

        /** @property {function(Object, ?Object=): String} */
        template:_.template(
            '<<%= tagName %> href="<%= link %>" class="action' +
                '<%= className ? " " + className : "" %>' +
                '<%= !enabled ? " disabled" : "" %>' +
                '"' +
                ' <%= attributesTemplate({attributes: attributes}) %>' +
                ' title="<%= label %>"' +
            '>' +
                '<% if (icon) { %>' +
                    '<i class="icon-<%= icon %> hide-text"><%= label %></i>' +
                '<% } else { %>' +
                    '<% if (iconClassName) { %>' +
                        '<i class="<%= iconClassName %>"></i>' +
                    '<% } %>' +
                    ' <%= label %>' +
                '<% } %>' +
            '</<%= tagName %>>'
        ),

        attributesTemplate: _.template(
            '<% _.each(attributes, function(attribute, name) { %>' +
                '<%= name %>="<%= attribute %>" ' +
            '<% }) %>'
        ),

        /** @property */
        events: {
            'click': 'onClick'
        },

        /**
         * Initialize
         *
         * @param {Object} options
         * @param {oro.datagrid.AbstractAction} options.action
         * @param {function(Object, ?Object=): string} [options.template]
         * @param {String} [options.label]
         * @param {String} [options.icon]
         * @param {String} [options.link]
         * @param {Boolean} [options.runAction]
         * @param {Boolean} [options.onClickReturnValue]
         * @throws {TypeError} If mandatory option is undefined
         */
        initialize: function(options) {
            options = options || {};
            if (!options.action) {
                throw new TypeError("'action' is required");
            }

            if (options.template) {
                this.template = options.template;
            }

            if (options.label) {
                this.label = options.label;
            }

            if (options.icon) {
                this.icon = options.icon;
            }

            if (options.link) {
                this.link = options.link;
            }

            if (options.iconClassName) {
                this.iconClassName = options.iconClassName;
            }

            if (options.className) {
                this.className = options.className;
            }

            if (_.has(options, 'runAction')) {
                this.runAction = options.runAction;
            }

            if (_.has(options, 'onClickReturnValue')) {
                this.onClickReturnValue = options.onClickReturnValue;
            }

            this.action = options.action;
            Backbone.View.prototype.initialize.apply(this, arguments);
        },

        /**
         * Render actions
         *
         * @return {*}
         */
        render: function () {
            this.$el.empty();

            var $el = $(this.template({
                label: this.label || this.action.label,
                icon: this.icon,
                className: this.className,
                iconClassName: this.iconClassName,
                link: this.link,
                action: this.action,
                attributes: this.attributes,
                attributesTemplate: this.attributesTemplate,
                enabled: this.enabled,
                tagName: this.tagName
            }));

            this.setElement($el);
            return this;
        },

        /**
         * Handle launcher click
         *
         * @protected
         * @return {Boolean}
         */
        onClick: function() {
            if (!this.enabled) {
                return this.onClickReturnValue;
            }
            this.trigger('click', this);
            if (this.runAction) {
                this.action.run();

                //  skip launcher functionality, if action was executed
                return false;
            }
            return this.onClickReturnValue;
        },

        /**
         * Disable
         *
         * @return {*}
         */
        disable: function() {
            this.enabled = false;
            this.$el.addClass('disabled');
            return this;
        },

        /**
         * Enable
         *
         * @return {*}
         */
        enable: function() {
            this.enabled = true;
            this.$el.removeClass('disabled');
            return this;
        }
    });
});
