/* jshint browser:true */
/* global define, require */
define(['jquery', 'underscore', 'oro/tools', 'oro/mediator', 'oro/datafilter/collection-filters-manager'],
function($, _, tools,  mediator, FiltersManager) {
    'use strict';

    var filterModuleName = 'oro/datafilter/{{type}}-filter',
        filterTypes = {
            string:      'choice',
            choice:      'select',
            selectrow:   'select-row',
            multichoice: 'multiselect',
            boolean:     'select'
        },
        methods = {
            initBuilder: function () {
                this.metadata = this.$el.data('metadata');
                this.modules = {};
                methods.collectModules.call(this);
                tools.loadModules(this.modules, _.bind(methods.build, this));
            },

            /**
             * Collects required modules
             */
            collectModules: function () {
                var modules = this.modules;
                _.each((this.metadata.filters || {}) || {}, function (filter) {
                     var type = filter.type;
                     modules[type] = filterModuleName.replace('{{type}}', filterTypes[type] || type);
                });
            },

            build: function () {
                var options = {};
                try {
                    options = methods.combineOptions.call(this);
                } catch (e) {
                    // @todo handle exception
                    console.log(e.stack);
                    console.error(e.message);
                }
                options.collection = this.collection;
                this.$el.prepend((new FiltersManager(options)).render().$el);
                mediator.trigger('datagrid_filters:rendered', this.collection);
            },

            /**
             * Process metadata and combines options for filters
             *
             * @returns {Object}
             */
            combineOptions: function () {
                 var options = {},
                     modules = this.modules;
                 // @TODO fix error in case when filters not isset
                 options.filters = _.map(this.metadata.filters, function (filter, name) {
                    return new (modules[filter.type].extend(_.extend({name: name}, filter.options)));
                 });
                 return options;
             }
        };

    mediator.on('datagrid_collection_set_after', function (collection, $el) {
        methods.initBuilder.call({$el: $el, collection: collection});
    });
});
