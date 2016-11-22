define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'backbone',
        'datepicker',
        'pim/date-context',
        'text!activity-manager/templates/widget/project-due-date'
    ],
    function ($, _, __, Backbone, Datepicker, DateContext, template) {
        'use strict';

        return Backbone.View.extend({
            template: _.template(template),
            dueDate: null,

            /**
             * Model date format
             */
            modelDateFormat: 'yyyy-MM-dd',

            /**
             * Date widget options
             */
            datetimepickerOptions: {
                format: DateContext.get('date').format,
                defaultFormat: DateContext.get('date').defaultFormat,
                language: DateContext.get('language')
            },

            /**
             * @param {String} dueDate
             */
            initialize: function (dueDate) {
                this.dueDate = dueDate;
            },

            /**
             * Render the given due date
             */
            render: function () {
                var localizedDueDate = this.formatDate(
                    this.dueDate,
                    this.modelDateFormat,
                    DateContext.get('date').format
                );

                this.$el.html(this.template({
                    dueDateLabel: __('activity_manager.widget.due_date'),
                    dueDate: localizedDueDate
                }));
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
                if (_.isEmpty(date) || _.isUndefined(date)) {
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
