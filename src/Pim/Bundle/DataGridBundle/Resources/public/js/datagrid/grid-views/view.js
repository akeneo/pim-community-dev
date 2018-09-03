/* global define */
define(['backbone', 'underscore', 'oro/translator', 'oro/datagrid/grid-views/collection'],
function (Backbone, _, __, GridViewsCollection) {
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
                    '<%=  current %>' + '<span class="caret"></span>' +
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

        /** @property */
        choices: [],

        /** @property */
        viewsCollection: GridViewsCollection,

        /**
         * Initializer.
         *
         * @param {Object} options
         * @param {Backbone.Collection} options.collection
         * @param {Boolean} [options.enable]
         * @param {Array}   [options.choices]
         * @param {Array}   [options.views]
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

            options.views = options.views || [];
            this.viewsCollection = new this.viewsCollection(options.views);

            Backbone.View.prototype.initialize.call(this, options);
        },

        /**
         * Disable view selector
         *
         * @return {*}
         */
        disable: function () {
            this.enabled = false;
            this.render();

            return this;
        },

        /**
         * Enable view selector
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
            var view = this.viewsCollection.get(gridView);

            if (view) {
                var viewState = _.extend({}, this.collection.initialState, view.toGridState());
                this.collection.updateState(viewState);
                this.collection.fetch();
            }

            return this;
        },

        render: function () {
            this.$el.empty();

            if (this.choices.length > 0) {
                var currentView = _.filter(
                    this.choices,
                    _.bind(function (item) {
                        return item.value == this.collection.state.gridView;
                    }, this)
                );

                var currentViewLabel = currentView.length ? _.first(currentView).label : __('pim_datagrid.view_selector.select');

                this.$el.append(
                    $(
                        this.template({
                            disabled: !this.enabled,
                            choices: this.choices,
                            current: currentViewLabel
                        })
                    )
                );
            }

            return this;
        }
    });
});
