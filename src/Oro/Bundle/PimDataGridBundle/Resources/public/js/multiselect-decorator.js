/* global define */
define(['jquery', 'underscore', 'oro/mediator', 'jquery.multiselect', 'jquery.multiselect.filter'],
function($, _, mediator) {
    'use strict';

    /**
     * Multiselect decorator class.
     * Wraps multiselect widget and provides design modifications
     *
     * @export oro/multiselect-decorator
     * @class  oro.MultiselectDecorator
     */
    var MultiselectDecorator = function(options) {
        this.initialize(options);
    };

    MultiselectDecorator.prototype = {
        /**
         * Multiselect widget element container
         *
         * @property {Object}
         */
        element: null,

        /**
         * Default multiselect widget parameters
         *
         * @property {Object}
         */
        parameters: {
            height: 'auto'
        },

        /**
         * @property {Boolean}
         */
        contextSearch: true,

        /**
         * Minimum width of this multiselect
         *
         * @property {int}
         */
        minimumWidth: null,

        /**
         * Initialize all required properties
         */
        initialize: function(options) {
            if (!options.element) {
                throw new Error('Select element must be defined');
            }
            this.element = options.element;

            if (options.parameters) {
                _.extend(this.parameters, options.parameters);
            }

            if (_.has(options, 'contextSearch')) {
                this.contextSearch = options.contextSearch;
            }

            // initialize multiselect widget
            this.multiselect(this.parameters);

            // initialize multiselect filter
            if (this.contextSearch) {
                this.multiselectfilter({
                    label: '',
                    placeholder: '',
                    autoReset: true
                });
            }

            // destroy DOM garbage after change page via hash-navigation
            mediator.once('hash_navigation_request:start', function() {
                if (this.element.closest('body').length) {
                    this.multiselect('destroy');
                    this.element.hide();
                }
            }, this);
        },

        /**
         * Set design for view
         *
         * @param {Backbone.View} view
         */
        setViewDesign: function(view) {
            view.$('.ui-multiselect').removeClass('ui-widget').removeClass('ui-state-default');
            view.$('.ui-multiselect span.ui-icon').remove();
        },

        /**
         * Action performed on dropdown open
         */
        onOpenDropdown: function() {
            this.getWidget().find('input[type="search"]').focus();
            $('body').trigger('click');
        },

        /**
         * Get minimum width of dropdown menu
         *
         * @return {Number}
         */
        getMinimumDropdownWidth: function() {
            if (_.isNull(this.minimumWidth)) {
                const margin = 100;
                const elements = this.getWidget().find('.ui-multiselect-checkboxes li');
                const longest = _.max(_.map(elements, function (element) {
                    return $(element).find('span:first').width();
                }));

                this.minimumWidth = longest + margin;
            }

            return this.minimumWidth;
        },

        /**
         * Get multiselect widget
         *
         * @return {Object}
         */
        getWidget: function() {
            try {
                return this.multiselect('widget');
            } catch (error) {
                return $('.ui-multiselect-menu.pimmultiselect');
            }
        },

        /**
         * Proxy for multiselect method
         *
         * @param functionName
         * @return {Object}
         */
        multiselect: function(functionName) {
            return this.element.multiselect(functionName);
        },

        /**
         * Proxy for multiselectfilter method
         *
         * @param functionName
         * @return {Object}
         */
        multiselectfilter: function(functionName) {
            return this.element.multiselectfilter(functionName);
        }
    };

    return MultiselectDecorator;
});
