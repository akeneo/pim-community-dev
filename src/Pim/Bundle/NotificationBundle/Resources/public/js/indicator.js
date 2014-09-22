define(
    ['backbone', 'underscore'],
    function(Backbone, _) {
        'use strict';

        var Indicator = Backbone.Model.extend({
            defaults: {
                value: null,
                type: ''
            }
        });

        var IndicatorView = Backbone.View.extend({
            model: Indicator,

            template: _.template('<span class="badge<%= type ? \' badge-\' + type : \'\' %>"><%= value %></span>'),

            initialize: function() {
                this.listenTo(this.model, 'change', this.render);

                this.render();
            },

            render: function() {
                this.$el.html(
                    this.template({
                        value: this.model.get('value'),
                        type: this.model.get('type')
                    })
                );

                return this;
            }
        });

        return function(opts) {
            var options = _.extend({}, { el: null }, opts);
            var indicator = new Indicator(options);
            var indicatorView = new IndicatorView({el: options.el, model: indicator});
            indicator.setElement = function() {
                indicatorView.setElement.apply(indicatorView, arguments);
                return indicatorView.render();
            };

            return indicator;
        };
    }
);
