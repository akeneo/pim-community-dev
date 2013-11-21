/* jshint browser:true */
/* global define, require */
define(['jquery', 'underscore', 'oro/tools', 'oro/mediator', 'oro/query-designer/filter-manager'],
function($, _, tools, mediator, FilterManager) {
    'use strict';

    var
        initialized = false,
        filterModuleName = 'oro/datafilter/{{type}}-filter',
        filterTypes = {
            string:      'choice',
            choice:      'select',
            selectrow:   'select-row',
            multichoice: 'multiselect',
            boolean:     'select'
        },
        methods = {
            /**
             * Initializes data filters
             */
            initBuilder: function () {
                var metadata = this.$el.closest('[data-metadata]').data('metadata');
                this.metadata = _.extend({filters: []}, metadata);
                this.metadata.filters.push({type: 'none', applicable: {}});
                this.modules = {};
                methods.collectModules.call(this);
                tools.loadModules(this.modules, _.bind(methods.build, this));
            },

            /**
             * Collects required modules
             */
            collectModules: function () {
                var modules = this.modules;
                _.each(this.metadata.filters || {}, function (filter) {
                    var type = filter.type;
                    modules[type] = filterModuleName.replace('{{type}}', filterTypes[type] || type);
                });
            },

            /**
             * Builds data filters
             */
            build: function () {
                var options = methods.combineOptions.call(this);
                var manager = new FilterManager(options);
                this.$el.prepend(manager.render().$el);
                mediator.trigger('query_designer_filter_manager_initialized', manager);
            },

            /**
             * Process metadata and combines options for filters
             *
             * @returns {Object}
             */
            combineOptions: function () {
                var filters = {},
                    modules = this.modules;
                _.each(this.metadata.filters, function (options) {
                    filters[options.type] = new (modules[options.type].extend(options));
                });
                return {filters: filters};
            }
        };

    /**
     * @export  oro/query-designer/filter-builder
     * @class   oro.queryDesigner.filterBuilder
     */
    return {
        /**
         * Initializes query designer filters
         *
         * @param {jQuery} $el Container
         * @param {Function} callback A function which should be called when the initialization finished
         */
        init: function ($el, callback) {
            var initializedHandler = _.bind(function (manager) {
                initialized = true;
                callback(manager);
            }, this);
            mediator.once('query_designer_filter_manager_initialized', initializedHandler);

            methods.initBuilder.call({$el: $el});

            mediator.once('hash_navigation_request:start', function() {
                if (!initialized) {
                    mediator.off('query_designer_filter_manager_initialized', initializedHandler);
                }
            });
        }
    };
});
