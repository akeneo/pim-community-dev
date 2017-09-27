define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'backbone',
        'routing',
        'oro/loading-mask',
        'oro/mediator',
        'pim/router'
    ],
    function ($, _, __, Backbone, Routing, LoadingMask, mediator) {
        'use strict';

        return Backbone.View.extend({
            defaults: {
                delayedLoadTimeout: 1000,
                minRefreshInterval: 20000,
                alias:              null
            },

            options: {},

            data: {},

            loadingMask: null,

            loadTimeout: null,

            needsData: true,

            initialize: function (options) {
                this.options = _.extend({}, this.defaults, this.options, options);

                mediator.on('route_complete', function (loadedRoute) {
                    if (loadedRoute === 'pim_dashboard_index') {
                        this.needsData = true;
                        this.delayedLoad();
                    }
                }, this);
            },

            render: function () {
                this.$el.html(this.template({ data: this.data, options: this.options, __: __ }));

                return this;
            },

            setElement: function () {
                Backbone.View.prototype.setElement.apply(this, arguments);

                this._createLoadingMask();

                return this;
            },

            loadData: function () {
                if (!this.needsData) {
                    this.loadTimeout = null;

                    return;
                }
                this.needsData = false;
                this._beforeLoad();

                $.get(Routing.generate('pim_dashboard_widget_data', { alias: this.options.alias }))
                    .then(_.bind(function (resp) {
                        this.data = this._processResponse(resp);
                        this.render();
                        this._afterLoad();
                    }, this));
            },

            reload: function () {
                this.needsData = true;

                this.loadData();
            },

            delayedLoad: function () {
                if (!this.loadTimeout) {
                    this.loadTimeout = setTimeout(_.bind(function () {
                        this.loadData();
                    }, this), this.options.delayedLoadTimeout);
                }
            },

            _beforeLoad: function () {
                this.$el.parent().addClass('loading');
                this.loadingMask.show();
            },

            _afterLoad: function () {
                this.$el.parent().removeClass('loading');
                this.loadingMask.hide();
                this.loadTimeout = null;
                setTimeout(_.bind(function () {
                    this.needsData = true;
                }, this), this.options.minRefreshInterval);
            },

            _createLoadingMask: function () {
                if (this.loadingMask) {
                    this.loadingMask.remove();
                }
                this.loadingMask = new LoadingMask();
                this.loadingMask.render().$el.insertAfter(this.$el);
            },

            _processResponse: function (data) {
                return data;
            }
        });
    }
);
