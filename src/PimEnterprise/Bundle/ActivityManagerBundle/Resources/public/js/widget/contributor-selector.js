define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'backbone',
        'pim/fetcher-registry',
        'activity-manager/widget/contributor-selector-line',
        'text!activity-manager/templates/widget/contributor-selector'
    ],
    function ($, _, __, Backbone, FetcherRegistry, ContributorSelectorLine, template) {
        'use strict';

        return Backbone.View.extend({
            template: _.template(template),
            projectCode: null,
            resultsPerPage: 2,
            queryTimer: null,
            searchParameters: {},

            /**
             * @param {int} projectCode
             */
            initialize: function (projectCode) {
                this.projectCode = projectCode;
            },

            /**
             * Render a select2 populated by contributors of the given project
             */
            render: function () {
                this.$el.html(this.template());

                this.initializeSelect();
            },

            /**
             * Initialize a select2 populated by contributors of the given project
             */
            initializeSelect: function () {
                var $select = this.$('.contributor-selector-select2');
                var options = {
                    query: function (options) {
                        clearTimeout(this.queryTimer);
                        this.queryTimer = setTimeout(function () {
                            var page = 1;

                            if (options.context && options.context.page) {
                                page = options.context.page;
                            }
                            var searchParameters = this.getSelectSearchParameters(options.term, page);

                            FetcherRegistry
                                .getFetcher('project')
                                .searchContributors(this.projectCode, searchParameters).then(function (contributors) {
                                    var choices = this.toSelect2Format(contributors);

                                    if (1 === page && '' === searchParameters.search) {
                                        choices.unshift({id:0, text:__('activity_manager.widget.all_contributors')});
                                    }

                                    options.callback({
                                        results: choices,
                                        more: choices.length >= this.resultsPerPage,
                                        context: {
                                            page: page + 1
                                        }
                                    });
                                }.bind(this)
                            );
                        }.bind(this), 400);
                    }.bind(this),

                    initSelection: function (element, callback) {
                        callback({id:0, text:__('activity_manager.widget.all_contributors')});
                    }.bind(this),

                    formatResult: function (item, $container) {
                        var contributorSelectorLine = new ContributorSelectorLine(item.text, 'Line');

                        contributorSelectorLine.render();
                        $container.append(contributorSelectorLine.$el);
                    },

                    formatSelection: function (item, $container) {
                        var contributorSelectorLine = new ContributorSelectorLine(item.text.toLowerCase(), 'Current');

                        contributorSelectorLine.render();
                        $container.append(contributorSelectorLine.$el);
                    },

                    dropdownCssClass: 'AknProjectWidget-select2Dropdown' +
                    ' AknProjectWidget-select2Dropdown--arrowLeft' +
                    ' activity-manager-widget-contributor-dropdown'
                };

                $select.select2(options);
                $select.on('change', function (event) {
                    this.trigger('activity-manager.widget.contributor-selected', event);
                }.bind(this));
                $select.on('select2-open', function() {
                    $('.activity-manager-widget-contributor-dropdown .select2-search')
                        .prepend('<i class="icon-search AknProjectWidget-select2SearchIcon"></i>');
                    $('.activity-manager-widget-contributor-dropdown .select2-input')
                        .attr('placeholder', __('activity_manager.widget.placeholder.contributor_selector'));
                });
                $select.on('select2-close', function() {
                    $('.activity-manager-widget-contributor-dropdown .select2-search .icon-search').remove();
                    $('.activity-manager-widget-contributor-dropdown .select2-input').attr('placeholder', null);
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
                return _.map(data, function (contributor) {
                    return {
                        text: contributor.firstName + ' ' + contributor.lastName,
                        id: contributor.username
                    };
                });
            },

            /**
             * Returns the project code
             *
             * @returns {String}
             */
            getProjectCode: function () {
                return this.projectCode;
            }
        });
    }
);
