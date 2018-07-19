'use strict';

/**
 * Module to display a line in the Select2 dropdown of the Datagrid View Selector.
 * This module accepts extensions to display more info beside the view.
 *
 * @author    Adrien Petremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/i18n',
        'pimcommunity/grid/view-selector/line',
        'pim/user-context',
        'pim/date-context',
        'pim/formatter/date',
        'pim/template/grid/view-selector/line',
        'teamwork-assistant/templates/grid/view-selector/line-project'
    ],
    function (
        $,
        _,
        __,
        i18n,
        ViewSelectorLine,
        userContext,
        DateContext,
        DateFormatter,
        templateView,
        templateProject
    ) {
        return ViewSelectorLine.extend({
            templates: {
                view: _.template(templateView),
                project: _.template(templateProject)
            },

            /**
             * {@inheritdoc}
             *
             * Render a different template with different values depending on the view type of this line.
             */
            render: function () {
                var template = this.templates[this.datagridViewType];
                var data = {};

                if ('view' === this.datagridViewType) {
                    data = this.prepareViewData();
                } else if ('project' === this.datagridViewType) {
                    data = this.prepareProjectData();
                }

                this.$el.html(template(data));
                this.renderExtensions();

                return this;
            },

            /**
             * Prepare the view data for the template.
             *
             * @returns {Object}
             */
            prepareViewData: function () {
                return {
                    view: this.datagridView,
                    isCurrent: (this.currentViewId === this.datagridView.id)
                };
            },

            /**
             * Prepare the project data for the template.
             *
             * @returns {Object}
             */
            prepareProjectData: function () {
                var project = this.datagridView;
                var badgeClass = 'AknBadge--warning';

                if (project.completeness.ratio_done === 0) {
                    badgeClass = 'AknBadge--invalid';
                } else if (project.completeness.ratio_done === 100) {
                    badgeClass = 'AknBadge--success';
                }

                var dateFormat = DateContext.get('date').format;

                return {
                    project: project,
                    dueDateLabel: __('teamwork_assistant.project.due_date'),
                    dueDate: DateFormatter.format(project.due_date, 'yyyy-MM-dd', dateFormat),
                    channelLabel: i18n.getLabel(
                        project.channel.labels,
                        userContext.get('uiLocale'),
                        project.channel.code
                    ),
                    localeLabel: project.locale.label,
                    isCurrent: (this.currentViewId === project.datagridView.id),
                    completionRatio: Math.round(project.completeness.ratio_done),
                    badgeClass: badgeClass
                };
            }
        });
    }
);
