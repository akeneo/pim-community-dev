define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'backbone',
        'pim/fetcher-registry',
        'activity-manager/widget/project-selector-line',
        'text!activity-manager/templates/widget/project-selector'
    ],
    function ($, _, __, Backbone, FetcherRegistry, ProjectSelectorLine, template) {
        'use strict';

        return Backbone.View.extend({
            template: _.template(template),
            resultsPerPage: 2,
            queryTimer: null,
            searchParameters: {},

            render: function () {
                this.$el.html(this.template());

                this.initializeSelect();
            },

            initializeSelect: function () {
                var $select = this.$('.project-selector-select2');

                var options = {
                    query: function (options) {
                        clearTimeout(this.queryTimer);
                        this.queryTimer = setTimeout(function () {
                            var page = 1;

                            if (options.context && options.context.page) {
                                page = options.context.page;
                            }
                            var searchParameters = this.getSelectSearchParameters(options.term, page);

                            FetcherRegistry.getFetcher('project').search(searchParameters).then(function (projects) {
                                var choices = this.toSelect2Format(projects);

                                options.callback({
                                    results: choices,
                                    more: choices.length >= this.resultsPerPage,
                                    context: {
                                        page: page + 1
                                    }
                                });
                            }.bind(this));
                        }.bind(this), 400);
                    }.bind(this),

                    initSelection: function (element, callback) {
                        var searchOptions = {search: null, limit: 1, page: 1};

                        FetcherRegistry.getFetcher('project').search(searchOptions).then(function (projects) {
                            var project = this.toSelect2Format(projects);

                            callback(project[0]);
                        }.bind(this));
                    }.bind(this),

                    formatResult: function (item, $container) {
                        var projectSelectorLine = new ProjectSelectorLine(item, 'Line');

                        projectSelectorLine.render();
                        $container.append(projectSelectorLine.$el);
                    },

                    formatSelection: function (item, $container) {
                        var projectSelectorLine = new ProjectSelectorLine(item, 'Current');

                        projectSelectorLine.render();
                        $container.append(projectSelectorLine.$el);
                    },

                    dropdownCssClass: 'AknProjectWidget-select2Dropdown' +
                        ' AknProjectWidget-select2Dropdown--arrowRight' +
                        ' activity-manager-widget-project-dropdown'
                };

                $select.select2(options);
                $select.on('change', function (event) {
                    this.trigger('activity-manager.widget.project-selected', event);
                }.bind(this));
                $select.on('select2-open', function() {
                    $('.activity-manager-widget-project-dropdown .select2-search')
                        .prepend('<i class="icon-search AknProjectWidget-select2SearchIcon"></i>');
                    $('.activity-manager-widget-project-dropdown .select2-input')
                        .attr('placeholder', __('activity_manager.widget.placeholder.project_selector'));
                });
                $select.on('select2-close', function() {
                    $('.activity-manager-widget-project-dropdown .select2-search .icon-search').remove();
                    $('.activity-manager-widget-project-dropdown .select2-input').attr('placeholder', null);
                });
            },

            /**
             * Get fetcher search parameters by giving select2 search term & page
             *
             * @param {string} term
             * @param {int}    page
             *
             * @return {Object}
             */
            getSelectSearchParameters: function (term, page) {
                return $.extend(true, {}, this.searchParameters, {
                    search: term,
                    options: {
                        limit: this.resultsPerPage,
                        page: page
                    }
                });
            },

            /**
             * Take incoming data and format them to have all required parameters
             * to be used by the select2 module.
             *
             * @param {array} data
             *
             * @returns {array}
             */
            toSelect2Format: function (data) {
                return _.map(data, function (project) {
                    return {
                        id: project.code,
                        text: project.label,
                        label: project.label,
                        due_date: project.due_date,
                        description: project.description,
                        owner: project.owner,
                        channel: project.channel,
                        locale: project.locale
                    };
                });
            }
        });
    }
);
