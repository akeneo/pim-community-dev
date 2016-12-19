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
        'backbone',
        'datepicker',
        'pim/form',
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
        Backbone,
        Datepicker,
        BaseForm,
        userContext,
        DateContext,
        templateView,
        templateProject
    ) {
        return BaseForm.extend({
            templateView: _.template(templateView),
            templateProject: _.template(templateProject),
            datagridView: null,

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
             */
            configure: function (datagridView) {
                this.datagridView = datagridView;

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (_.has(this.datagridView, 'due_date')) {
                    var project = this.datagridView;
                    var completionPercentage = 47; // TODO: CHANGE WITH REAL VALUE
                    var completionStatus = 'wip';

                    if (completionPercentage === 0) {
                        completionStatus = 'todo';
                    } else if (completionPercentage === 100) {
                        completionStatus = 'done';
                    }

                    var dateFormat = DateContext.get('date').format;

                    this.$el.html(this.templateProject({
                        project: project,
                        dueDateLabel: __('activity_manager.project.due_date'),
                        dueDate: this.formatDate(project.due_date, this.modelDateFormat, dateFormat),
                        channelLabel: i18n.getLabel(
                            project.channel.labels,
                            userContext.get('uiLocale'),
                            project.channel.code
                        ),
                        localeLabel: project.locale.label,
                        isCurrent: this.getRoot().currentView.id == project.id,
                        completionPercentage: completionPercentage,
                        completionStatus: completionStatus
                    }));
                } else {
                    this.$el.html(this.templateView({
                        view: this.datagridView,
                        isCurrent: this.getRoot().currentView.id == this.datagridView.id
                    }));
                }

                this.renderExtensions();

                return this;
            },

            /**
             * Format a date according to specified format.
             * It instantiates a datepicker on-the-fly to perform the conversion. Not possible to use the "real" ones since
             * we need to format a date even when the UI is not initialized yet.
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
