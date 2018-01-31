define(
    ['jquery', 'backbone', 'underscore', 'oro/translator'],
    function ($, Backbone, _, __) {
        'use strict';
        var interval;
        var loading = false;

        var JobExecution = Backbone.Model.extend({
            path: null,
            initialize: function (params) {
                if (!_.has(params, 'path')) {
                    throw new Error('A "path" parameter is required');
                }
                this.path = params.path;
                Backbone.Model.prototype.initialize.apply(this, arguments);
            },
            url: function () {
                return this.path;
            }
        });

        var JobExecutionView = Backbone.View.extend({
            showLabel: __('job_execution.summary.display_item'),
            hideLabel: __('job_execution.summary.hide_item'),

            initialize: function (params) {
                this.showLabel            = params.showLabel || this.showLabel;
                this.hideLabel            = params.hideLabel || this.hideLabel;
                this.loadingImageSelector = params.loadingImageSelector;

                this.listenTo(this.model, 'change', this.render);
                this.model.bind('request', this.ajaxStart, this);
                this.model.bind('sync', this.ajaxComplete, this);
                this.model.bind('error', this.ajaxError, this);
            },

            ajaxStart: function () {
                loading = true;
                $(this.loadingImageSelector).removeClass('transparent');
            },

            ajaxComplete: function (model, resp) {
                $(this.loadingImageSelector).addClass('transparent');
                if (!resp.jobExecution.isRunning) {
                    clearInterval(interval);
                    interval = null;
                }
                loading = false;
            },

            ajaxError: function (model, resp, options) {
                $(this.loadingImageSelector).addClass('transparent');
                clearInterval(interval);
                interval = null;
                this.$el.html(
                    '<tr><td colspan="5"><span class="AknBadge AknBadge--important">' +
                        options.xhr.statusText +
                    '</span></td></tr>'
                );
                loading = false;
            },

            events: {
                'click a.data': 'toggleData'
            },

            toggleData: function (event) {
                event.preventDefault();

                var $link        = $(event.currentTarget);
                var displayLabel = $link.data('display-label');
                var hideLabel    = $link.data('hide-label');

                $link.siblings('table').toggleClass('hide');
                $link.text($link.text().trim() === displayLabel ? hideLabel : displayLabel);
            },

            template: _.template($('#job-execution-summary').html()),

            render: function () {
                this.$el.html(
                    this.template(
                        _.extend(
                            {
                                showLabel: this.showLabel,
                                hideLabel: this.hideLabel
                            },
                            this.model.toJSON()
                        )
                    )
                );

                return this;
            }
        });

        var JobExecutionStatusView = Backbone.View.extend({
            statusLabel: 'Status',
            initialize: function (params) {
                this.statusLabel = params.statusLabel || this.statusLabel;

                this.listenTo(this.model, 'change', this.render);
            },

            template: _.template($('#job-execution-status').html()),

            render: function () {
                this.$el.html(
                    this.template(
                        _.extend(
                            {
                                statusLabel: this.statusLabel
                            },
                            this.model.toJSON()
                        )
                    )
                );

                return this;
            }
        });

        var JobExecutionButtonsView = Backbone.View.extend({
            downloadFileRoute: null,
            executionId: null,

            initialize: function (params) {
                if (!_.has(params, 'downloadFileRoute')) {
                    throw new Error('A "downloadFileRoute" parameter is required');
                }
                if (!_.has(params, 'executionId')) {
                    throw new Error('A "executionId" parameter is required');
                }

                this.downloadFileRoute = params.downloadFileRoute;
                this.executionId       = params.executionId;

                this.listenTo(this.model, 'change', this.render);
            },

            template: _.template($('#job-execution-buttons').html()),

            render: function () {
                this.$el.html(
                    this.template(
                        _.extend(
                            {
                                downloadFileRoute: this.downloadFileRoute,
                                executionId: this.executionId
                            },
                            this.model.toJSON()
                        )
                    )
                );

                return this;
            }
        });

        var JobExecutionLogButtonView = Backbone.View.extend({
            downloadLogRoute: null,
            executionId: null,
            downloadLabel: 'Download log',

            initialize: function (params) {
                if (!_.has(params, 'downloadLogRoute')) {
                    throw new Error('A "downloadLogRoute" parameter is required');
                }
                if (!_.has(params, 'executionId')) {
                    throw new Error('A "executionId" parameter is required');
                }

                this.downloadLogRoute = params.downloadLogRoute;
                this.executionId      = params.executionId;
                this.downloadLabel    = params.downloadLabel || this.downloadLabel;

                this.listenTo(this.model, 'change', this.render);
            },

            template: _.template($('#job-execution-log-button').html()),

            render: function () {
                this.$el.html(
                    this.template(
                        _.extend(
                            {
                                downloadLogRoute: this.downloadLogRoute,
                                executionId: this.executionId,
                                downloadLabel: this.downloadLabel
                            },
                            this.model.toJSON()
                        )
                    )
                );

                return this;
            }
        });

        return {
            init: function (params) {
                if (!_.has(params, 'loadingImageSelector')) {
                    throw new Error('A "loadingImageSelector" parameter is required');
                }
                if (!_.has(params, 'refreshButtonSelector')) {
                    throw new Error('A "refreshButtonSelector" parameter is required');
                }

                var jobExecution = new JobExecution(params);
                loading = true;
                jobExecution.fetch();

                params.model = jobExecution;

                new JobExecutionView(_.extend(params, {el: params.jobExecutionSelector}));
                new JobExecutionStatusView(_.extend(params, {el: params.jobExecutionStatusSelector}));
                new JobExecutionButtonsView(_.extend(params, {el: params.jobExecutionButtonsSelector}));
                new JobExecutionLogButtonView(_.extend(params, {el: params.jobExecutionLogButtonSelector}));

                var displayRefreshLink = function () {
                    $(params.loadingImageSelector).hide();
                    $(params.refreshButtonSelector).removeClass('transparent');
                };

                interval = setInterval(function () {
                    if (!loading) {
                        jobExecution.fetch();
                    }
                }, 1000);

                // Clear interval when changing page to prevent continuing to sync object on other pages
                Backbone.Router.prototype.on('route', function () {
                    clearInterval(interval);
                });

                setTimeout(function () {
                    if (null !== interval) {
                        clearInterval(interval);
                        displayRefreshLink();
                    }
                }, 120000);
            }
        };
    }
);
