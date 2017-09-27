'use strict';

/**
 * Contributor selector for the teamwork assistant widget.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'backbone',
        'pim/user-context',
        'pim/fetcher-registry',
        'teamwork-assistant/templates/widget/contributor-selector',
        'teamwork-assistant/templates/widget/contributor-selector-line'
    ],
    function ($, _, __, BaseForm, Backbone, UserContext, FetcherRegistry, template, lineTemplate) {
        return BaseForm.extend({
            template: _.template(template),
            lineTemplate: _.template(lineTemplate),
            resultsPerPage: 10,
            queryTimer: null,
            searchParameters: {},

            /**
             * Render a select2 populated by contributors of the given project
             */
            render: function () {
                this.$el.html('');

                if (UserContext.get('username') !== this.getFormData().currentProject.owner.username) {
                    return;
                }

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
                                .getFetcher('contributor')
                                .search(searchParameters).then(function (contributors) {
                                    var choices = this.arrayToSelect2Format(contributors);

                                    if (1 === page && '' === searchParameters.search) {
                                        choices.unshift({
                                            id: '_all_contributors',
                                            text: __('teamwork_assistant.widget.all_contributors')
                                        });
                                    }

                                    options.callback({
                                        results: choices,
                                        more: choices.length === this.resultsPerPage,
                                        context: {
                                            page: page + 1
                                        }
                                    });
                                }.bind(this)
                            );
                        }.bind(this), 400);
                    }.bind(this),

                    initSelection: function (element, callback) {
                        var choice = {id: '_all_contributors', text: __('teamwork_assistant.widget.all_contributors')};

                        if (this.getFormModel().has('currentContributor')) {
                            choice = this.toSelect2Format(this.getFormData().currentContributor);
                        }

                        callback(choice);
                    }.bind(this),

                    formatResult: function (item, $container) {
                        $container.append(this.formatLine(item.text, 'Line'));
                    }.bind(this),

                    formatSelection: function (item, $container) {
                        $container.append(this.formatLine(item.text, 'Current'));
                    }.bind(this),

                    dropdownCssClass: 'select2-drop--forProjectWidget' +
                    ' select2--withArrowLeft' +
                    ' teamwork-assistant-widget-contributor-dropdown',

                    containerCssClass: 'select2--withoutBorder'
                };

                $select.select2(options);
                $select.on('change', function (event) {
                    var code = event.added.id;
                    if ('_all_contributors' === code) {
                        code = null;
                    }
                    this.trigger('teamwork-assistant:widget:contributor-selected', code);
                }.bind(this));
                $select.on('select2-open', function () {
                    $('.teamwork-assistant-widget-contributor-dropdown .select2-search')
                        .prepend('<i class="icon-search select2-searchIcon"></i>');
                    $('.teamwork-assistant-widget-contributor-dropdown .select2-input')
                        .attr('placeholder', __('teamwork_assistant.widget.placeholder.contributor_selector'));
                });
                $select.on('select2-close', function () {
                    $('.teamwork-assistant-widget-contributor-dropdown .select2-search .icon-search').remove();
                    $('.teamwork-assistant-widget-contributor-dropdown .select2-input').attr('placeholder', null);
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
                    identifier: this.getFormData().currentProjectCode,
                    search: term,
                    options: {
                        limit: this.resultsPerPage,
                        page: page
                    }
                });
            },

            /**
             * Take incoming contributors as array and format them to have all required parameters
             * to be used by the select2 module.
             *
             * @param {Array} contributors
             */
            arrayToSelect2Format: function (contributors) {
                return _.map(contributors, this.toSelect2Format);
            },

            /**
             * Take incoming contributor and format it to have all required parameters
             * to be used by the select2 module.
             *
             * @param {Object} contributor
             *
             * @return {Object}
             */
            toSelect2Format: function (contributor) {
                return {
                    text: contributor.firstName + ' ' + contributor.lastName,
                    id: contributor.username
                };
            },

            /**
             * Format selection or result line in select2.
             *
             * @param {String} username
             * @param {String} type
             *
             * @return {String}
             */
            formatLine: function (username, type) {
                return this.lineTemplate({
                    username: username,
                    type: type
                });
            }
        });
    }
);
