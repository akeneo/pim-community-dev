define(
    ['jquery', 'backbone', 'underscore', 'routing'],
    function ($, Backbone, _, Routing) {
        'use strict';
        var interval;

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
            showLabel: 'Display item',
            hideLabel: 'Hide item',

            initialize: function (params) {
                this.showLabel            = params.showLabel || this.showLabel;
                this.hideLabel            = params.hideLabel || this.hideLabel;
                this.loadingImageSelector = params.loadingImageSelector;

                this.listenTo(this.model, "change", this.render);
                this.model.bind('request', this.ajaxStart, this);
                this.model.bind('sync', this.ajaxComplete, this);
            },

            ajaxStart: function () {
                $(this.loadingImageSelector).removeClass('transparent');
            },

            ajaxComplete: function (model, resp) {
                $(this.loadingImageSelector).addClass('transparent');
                if (!resp.jobExecution.isRunning) {
                    clearInterval(interval);
                    interval = null;
                }
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
                $link.text($link.text().trim() == displayLabel ? hideLabel : displayLabel);
            },

            template: _.template(
                // Step execution information
                '<% _.each(jobExecution.stepExecutions, function (stepExecution) { %>' +
                    '<tr>' +
                        '<td><%= stepExecution.label %></td>' +
                        '<td><%= stepExecution.status %></td>' +
                        '<td>' +
                            '<table class="table-striped table-bordered table-hover">' +
                                '<% _.each(stepExecution.summary, function (value, key) { %>' +
                                    '<tr>' +
                                        '<td><%= key %></td>' +
                                        '<td><%= value %></td>' +
                                    '</tr>' +
                                '<% }); %>' +
                            '</table>' +
                        '</td>' +
                        '<td><%= stepExecution.startedAt %></td>' +
                        '<td><%= stepExecution.endedAt %></td>' +
                    '</tr>' +

                    //Step execution warnings
                    '<% _.each(stepExecution.warnings, function (warning) { %>' +
                        '<tr class="warning">' +
                            '<td colspan="5">' +
                                '<span class="title"><%= warning.label.toUpperCase() %></span>&nbsp;' +
                                '<%= warning.reason %><br />' +
                                '<a class="data" href="#"' +
                                    'data-display-label="<%= showLabel %>"' +
                                    'data-hide-label="<%= hideLabel %>">' +
                                    '<%= showLabel %>' +
                                '</a>' +
                                '<table class="hide table-striped table-bordered table-hover">' +
                                    '<% _.each(warning.item, function (value, key) { %>' +
                                        '<tr>' +
                                            '<td><%= key %></td>' +
                                            '<td><%= value %></td>' +
                                        '</tr>' +
                                    '<% }); %>' +
                                '</table>' +
                            '</td>' +
                        '</tr>' +
                    '<% }); %>' +

                    //Step execution failures
                    '<% _.each(stepExecution.failures, function (failure) { %>' +
                        '<tr class="error">' +
                            '<td colspan="5">' +
                                '<span class="title"><%= stepExecution.label.toUpperCase() %></span>&nbsp;' +
                                '<%= failure %>' +
                            '</td>' +
                        '</tr>' +
                    '<% }); %>' +

                '<% }); %>' +

                //Job execution failures
                '<% _.each(jobExecution.failures, function (failure) { %>' +
                    '<tr class="error">' +
                        '<td colspan="5">' +
                            '<span class="title"><%= label.toUpperCase() %></span>&nbsp;' +
                            '<%= failure %>' +
                        '</td>' +
                    '</tr>' +
                '<% }); %>'
            ),

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

            template: _.template('<li><%= statusLabel %>: <%= jobExecution.status %></li>'),

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

            template: _.template(
                '<% _.each(archives, function (archive) { %>' +
                    '<a class="btn no-hash icons-holder-text" title="<%= archive.name %>" href="' +
                    '<%= Routing.generate(downloadFileRoute, {id: executionId, archiver: archive.archiver, key: archive.key }) %>' +
                    '">' +
                        '<i class="icon-download"></i>' +
                        '<%= archive.name %>' +
                    '</a>&nbsp;' +
                '<% }); %>'
            ),

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

            template: _.template(
                '<% if (hasLog) { %>' +
                    '<a class="btn no-hash icons-holder-text" title="<%= downloadLabel %>" href="' +
                        '<%= Routing.generate(downloadLogRoute, {id: executionId}) %>' +
                    '">' +
                        '<i class="icon-download"></i>' +
                        '<%= downloadLabel %>' +
                    '</a>' +
                '<% } %>'
            ),

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
                    jobExecution.fetch();
                }, 1000);

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

