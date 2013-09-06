/* global define */
define(['jquery', 'underscore', 'backbone', 'routing', 'bootstrap'],
function($, _, Backbone, routing) {
    'use strict';

    /**
     * @export  oro/navigation/shortcuts/view
     * @class   oro.navigation.shortcuts.View
     * @extends Backbone.View
     */
    return Backbone.View.extend({
        options: {
            el: '.shortcuts .input-large',
            source: null
        },

        events: {
            'change': 'onChange'
        },

        data: {},

        cache: {},

        initialize: function() {
            this.$el.val('');
            this.$el.typeahead({
                source:_.bind(this.source, this)
            });
            this.$form = this.$el.closest('form');
        },

        source: function(query, process) {
            if (_.isArray(this.options.source)) {
                process(this.options.source);
            } else if (!_.isUndefined(this.cache[query])) {
                process(this.cache[query]);
            } else {
                var url = routing.generate(this.options.source, { 'query': query });
                $.get(url, _.bind(function(data) {
                    this.data = data;
                    var result = [];
                    _.each(data, function(item, key) {
                        result.push(key);
                    });
                    this.cache[query] = result;
                    process(result);
                }, this));
            }
        },

        onChange: function() {
            var key = this.$el.val(),
                dataItem;
            this.$el.val('');
            if (!_.isUndefined(this.data[key])) {
                dataItem = this.data[key];
                this.$form.attr("action", dataItem.url).submit();
            }
        }
    });
});
