/* jshint browser:true */
/* global define, require */
define(['jquery', 'underscore', 'oro/tools', 'oro/query-designer/filter-manager'],
function($, _, tools, FilterManager) {
    'use strict';

    var
        filterModuleName = 'oro/datafilter/{{type}}-filter',
        methods = {
            /**
             * Initializes data filters
             */
            initBuilder: function () {
                this.metadata = _.extend({filters: []}, this.$el.data('metadata'));
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
                    modules[filter.type] = filterModuleName.replace('{{type}}', filter.type);
                });
            },

            /**
             * Builds data filters
             */
            build: function () {
                var options = methods.combineOptions.call(this);
                var manager = new FilterManager(options);
                this.$el.append(manager.render().$el);
                this.$el.data('manager', manager);
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
         */
        init: function ($el) {
            var obj = {$el: $el};
            methods.initBuilder.call(obj);
        }
    };
});
