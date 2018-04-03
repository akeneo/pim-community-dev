define(
    ['backbone', 'underscore'],
    function (Backbone, _) {
        'use strict';

        var Indicator = Backbone.Model.extend({
            defaults: {
                value: null,
                className: 'AknNotificationMenu-count',
                emptyClass: 'AknNotificationMenu-count--hidden',
                nonEmptyClass: ''
            }
        });

        var IndicatorView = Backbone.View.extend({
            model: Indicator,

            template: _.template(
                '<span class="<%= className %> <%= value ? nonEmptyClass : emptyClass %>"><%= value %></span>'
            ),

            initialize: function () {
                this.listenTo(this.model, 'change', this.render);

                this.render();
            },

            render: function () {
                this.$el.html(this.template(this.model.toJSON()));

                return this;
            }
        });

        return function (opts) {
            var el = opts.el || null;
            delete opts.el;
            var indicator = new Indicator(opts);
            var indicatorView = new IndicatorView({el: el, model: indicator});
            indicator.setElement = function () {
                indicatorView.setElement.apply(indicatorView, arguments);

                return indicatorView.render();
            };

            return indicator;
        };
    }
);
