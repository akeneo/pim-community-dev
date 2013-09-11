/* global define */
define(['backbone', 'underscore', 'oro/translations'], function (Backbone, _, __) {
    'use strict';

    /**
     * Datagrid views widget
     *
     * @export oro/datagrid/grid-views
     * @class   oro.datagrid.GridViews
     * @extends Backbone.View
     */
    return Backbone.View.extend({
        /** @property */
        events: {
            "click a": "onChange"
        },

        /** @property */
        template: _.template(
            '<div class="btn-group ">' +
                '<button data-toggle="dropdown" class="btn dropdown-toggle <% if (disabled) { %>disabled<% } %>">' +
                    '<i class="icon icon-eye-open"></i>' + '<%=  current %>' + '<span class="caret"></span>' +
                '</button>' +
                '<ul class="dropdown-menu pull-right">' +
                    '<% _.each(choices, function (choice) { %>' +
                        '<li><a href="#" data-value="' + '<%= choice.value %>' + '">' + '<%= choice.label %>' + '</a></li>' +
                    '<% }); %>' +
                '</ul>' +
            '</div>'
        ),

        /** @property */
        enabled: true,

        choices: [],

        /**
         * Initializer.
         *
         * @param {Object} options
         * @param {Backbone.Collection} options.collection
         * @param {Array} [options.items]
         */
        initialize: function (options) {
            options = options || {};

            if (!options.collection) {
                throw new TypeError("'collection' is required");
            }

            if (options.choices) {
                this.choices = _.union(this.choices, options.choices);
            }

            this.collection = options.collection;
            this.enabled = options.enable != false;

            this.listenTo(this.collection, "updateState", this.render);
            this.listenTo(this.collection, "beforeFetch", this.render);

            Backbone.View.prototype.initialize.call(this, options);
        },

        /**
         * Disable page size
         *
         * @return {*}
         */
        disable: function () {
            this.enabled = false;
            this.render();
            return this;
        },

        /**
         * Enable page size
         *
         * @return {*}
         */
        enable: function () {
            this.enabled = true;
            this.render();
            return this;
        },


        /**
         * Select change event handler
         *
         * @param {Event} e
         */
        onChange: function (e) {
            e.preventDefault();
            var value = $(e.target).data('value');
            if (value !== this.collection.state.gridView) {
                this.changeView(value);
            }
        },

        /**
         * Updates collection
         *
         * @param gridView
         * @returns {*}
         */
        changeView: function (gridView) {
            this.collection.state.gridView = gridView;
            this.collection.fetch();

            return this;
        },

        render: function () {
            this.$el.empty();

            var currentView = _.filter(
                this.choices,
                _.bind(
                    function (item) {
                        return item.value == this.collection.state.gridView;
                    },
                    this
                )
            );

            var currentViewLabel = currentView.length ? _.first(currentView).label : '';

            this.$el.append(
                $(
                    this.template({
                        disabled: !this.enabled,
                        choices: this.choices,
                        current: currentViewLabel
                    })
                )
            );

            return this;
        }
    });
});
