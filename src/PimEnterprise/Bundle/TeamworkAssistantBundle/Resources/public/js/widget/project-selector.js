'use strict';

/**
 * Project selector.
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
        'pim/i18n',
        'pim/fetcher-registry',
        'pim/user-context',
        'teamwork-assistant/templates/widget/project-selector',
        'teamwork-assistant/templates/widget/project-selector-line'
    ],
    function ($, _, __, BaseForm, Backbone, i18n, FetcherRegistry, UserContext, template, lineTemplate) {
        return BaseForm.extend({
            template: _.template(template),
            lineTemplate: _.template(lineTemplate),
            resultsPerPage: 20,
            queryTimer: null,
            searchParameters: {},
            className: 'AknButtonList-item',

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template());

                this.initializeSelect();
            },

            /**
             * Initialize the select2
             */
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
                                var choices = this.arrayToSelect2Format(projects);

                                options.callback({
                                    results: choices,
                                    more: choices.length === this.resultsPerPage,
                                    context: {
                                        page: page + 1
                                    }
                                });
                            }.bind(this));
                        }.bind(this), 400);
                    }.bind(this),

                    initSelection: function (element, callback) {
                        callback(this.toSelect2Format(this.getFormData().currentProject));
                    }.bind(this),

                    formatResult: function (item, $container) {
                        $container.append(this.formatLine(item, 'Line'));
                    }.bind(this),

                    formatSelection: function (item, $container) {
                        $container.append(this.formatLine(item, 'Current'));
                    }.bind(this),

                    dropdownCssClass: 'select2-drop--forProjectWidget' +
                        ' select2--withArrowRight' +
                        ' teamwork-assistant-widget-project-dropdown',

                    containerCssClass: 'select2--withoutBorder'
                };

                $select.select2(options);
                $select.on('change', function (event) {
                    this.trigger('teamwork-assistant:widget:project-selected', event.added.id);
                }.bind(this));
                $select.on('select2-open', function () {
                    $('.teamwork-assistant-widget-project-dropdown .select2-input')
                        .attr('placeholder', __('teamwork_assistant.widget.placeholder.project_selector'));
                });
                $select.on('select2-close', function () {
                    $('.teamwork-assistant-widget-project-dropdown .select2-input').attr('placeholder', null);
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
                        page: page,
                        completeness: 0
                    }
                });
            },

            /**
             * Take incoming data as array and format them to have all required parameters
             * to be used by the select2 module.
             *
             * @param {Array} data
             *
             * @return {Array}
             */
            arrayToSelect2Format: function (data) {
                return _.map(data, this.toSelect2Format);
            },

            /**
             * Take incoming project and format it to have all required parameters
             * to be used by the select2 module.
             *
             * @param {Object} project
             *
             * @return {Object}
             */
            toSelect2Format: function (project) {
                project.id = project.code;
                project.text = project.label;

                return project;
            },

            /**
             * Return the select2 line template
             *
             * @param {Object} project
             * @param {String} type
             *
             * @return {String}
             */
            formatLine: function (project, type) {
                var uiLocale = UserContext.get('uiLocale');
                var channelLabel = i18n.getLabel(project.channel.labels, uiLocale, project.channel.code);

                return this.lineTemplate({
                    type: type,
                    projectLabel: project.label,
                    projectChannel: channelLabel,
                    projectLocale: project.locale.label
                });
            }
        });
    }
);
