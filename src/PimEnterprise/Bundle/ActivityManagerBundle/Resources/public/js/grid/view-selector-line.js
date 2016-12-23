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
        'datepicker',
        'pim/grid/view-selector/line',
        'pim/user-context',
        'pim/date-context',
        'text!pim/template/grid/view-selector/line',
        'text!activity-manager/templates/grid/view-selector/line-project'
    ],
    function (
        $,
        _,
        __,
        i18n,
        Datepicker,
        ViewSelectorLine,
        userContext,
        DateContext,
        templateView,
        templateProject
    ) {
        return ViewSelectorLine.extend({
            templates: {
                view: _.template(templateView),
                project: _.template(templateProject)
            },

            /**
             * Date widget options
             */
            datetimepickerOptions: {
                format: DateContext.get('date').format,
                defaultFormat: DateContext.get('date').defaultFormat,
                language: DateContext.get('language')
            },

            /**
             * Model date format
             */
            modelDateFormat: 'yyyy-MM-dd',

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
                    isCurrent: this.isCurrentView
                };
            },

            /**
             * Prepare the project data for the template.
             *
             * @returns {Object}
             */
            prepareProjectData: function () {
                var project = this.datagridView;
                var completionPercentage = 47; // TODO: CHANGE WITH REAL VALUE
                var completionStatus = 'wip';

                if (completionPercentage === 0) {
                    completionStatus = 'todo';
                } else if (completionPercentage === 100) {
                    completionStatus = 'done';
                }

                var dateFormat = DateContext.get('date').format;

                return {
                    project: project,
                    dueDateLabel: __('activity_manager.project.due_date'),
                    dueDate: this.formatDate(project.due_date, this.modelDateFormat, dateFormat),
                    channelLabel: i18n.getLabel(
                        project.channel.labels,
                        userContext.get('uiLocale'),
                        project.channel.code
                    ),
                    localeLabel: project.locale.label,
                    isCurrent: this.isCurrentView,
                    completionPercentage: completionPercentage,
                    completionStatus: completionStatus
                };
            },

            /**
             * Format a date according to specified format.
             * It instantiates a datepicker on-the-fly to perform the conversion.
             * Not possible to use the "real" ones since we need to format a date even when the UI
             * is not initialized yet.
             *
             * @param {String} date
             * @param {String} fromFormat
             * @param {String} toFormat
             *
             * @return {String}
             */
            formatDate: function (date, fromFormat, toFormat) {
                if (_.isArray(date) || _.isEmpty(date)) {
                    return null;
                }

                var options = $.extend({}, this.datetimepickerOptions, {format: fromFormat});
                var fakeDatepicker = Datepicker.init($('<input>'), options).data('datetimepicker');

                fakeDatepicker.setValue(date);
                fakeDatepicker.format = toFormat;
                fakeDatepicker._compileFormat();

                return fakeDatepicker.formatDate(fakeDatepicker.getDate());
            }
        });
    }
);
