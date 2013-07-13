var navigation = navigation || {};
navigation.shortcut = navigation.shortcut || {};

navigation.shortcut.MainView = Backbone.View.extend({
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
            var url =  Routing.generate(this.options.source, { 'query': query });
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
        var key = this.$el.val();
        this.$el.val('');
        if (!_.isUndefined(this.data[key])) {
            var dataItem = this.data[key];
            this.$form.attr("action", dataItem.url).submit();
        }
    }
});
