'use strict';

define([
    'jquery',
    'underscore',
    'oro/translator',
    'pim/filter/filter',
    'text!pim/template/filter/attribute/date',
    'pim/fetcher-registry',
    'pim/user-context',
    'pim/i18n',
    'datepicker',
    'pim/date-context',
    'jquery.select2'
], function (
    $,
    _,
    __,
    BaseFilter,
    template,
    FetcherRegistry,
    UserContext,
    i18n,
    Datepicker,
    DateContext
) {
    return BaseFilter.extend({
        shortname: 'date',
        template: _.template(template),
        events: {
            'change [name^="filter-"]': 'updateState'
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
         * {@inherit}
         */
        initialize: function (config) {
            this.config = config.config;

            return BaseFilter.prototype.initialize.apply(this, arguments);
        },

        /**
         * {@inherit}
         */
        isEmpty: function () {
            var value = this.getValue();

            if (_.contains(['BETWEEN', 'NOT BETWEEN'], this.getOperator()) &&
                (undefined === value || !_.isArray(value) || _.isEmpty(value[0]) || _.isEmpty(value[1]))
            ) {
                return true;
            }

            if (!_.contains(['EMPTY', 'NOT EMPTY'], this.getOperator()) &&
                (undefined === value || '' === value)
            ) {
                return true;
            }

            return false;
        },

        /**
         * Initializes select2 and datepicker after rendering.
         */
        postRender: function () {
            var startDate = this.$('.start-date-wrapper:first');
            var endDate = this.$('.end-date-wrapper:first');

            this.$('[name="filter-operator"]').select2();

            if (0 !== startDate.length) {
                Datepicker
                    .init(startDate, this.datetimepickerOptions)
                    .on('changeDate', this.updateState.bind(this));
            }
            if (0 !== endDate.length) {
                Datepicker
                    .init(endDate, this.datetimepickerOptions)
                    .on('changeDate', this.updateState.bind(this));
            }
        },

        /**
         * {@inherit}
         */
        renderInput: function () {
            var dateFormat = DateContext.get('date').format;
            var value = this.getValue();
            var startValue = this.formatDate(value, this.modelDateFormat, dateFormat);
            var endValue = null;

            if (_.isArray(value)) {
                startValue = this.formatDate(value[0], this.modelDateFormat, dateFormat);
                endValue = this.formatDate(value[1], this.modelDateFormat, dateFormat);
            }
            if (undefined === this.getOperator()) {
                this.setOperator(_.first(_.values(this.config.operators)));
            }

            return this.template({
                isEditable: this.isEditable(),
                __: __,
                shortName: this.shortname,
                field: this.getField(),
                operator: this.getOperator(),
                startValue: startValue,
                endValue: endValue,
                operatorChoices: this.config.operators
            });
        },

        /**
         * {@inherit}
         */
        getTemplateContext: function () {
            return $.when(
                BaseFilter.prototype.getTemplateContext.apply(this, arguments),
                FetcherRegistry
                    .getFetcher('attribute')
                    .fetch(this.getField())
            ).then(function (templateContext, attribute) {
                return _.extend({}, templateContext, {
                    label: i18n.getLabel(attribute.labels, UserContext.get('uiLocale'), attribute.code)
                });
            });
        },

        /**
         * {@inherit}
         */
        updateState: function () {
            this.$('.start-date-wrapper').datetimepicker('hide');
            this.$('.end-date-wrapper').datetimepicker('hide');

            var value    = null;
            var operator = this.$('[name="filter-operator"]').val();

            if (!_.contains(['EMPTY', 'NOT EMPTY'], operator)) {
                var dateFormat = DateContext.get('date').format;
                var startValue = this.$('[name="filter-value-start"]').val();
                var formattedStartVal = this.formatDate(startValue, dateFormat, this.modelDateFormat);
                var valueEndField = this.$('[name="filter-value-end"]');

                value = formattedStartVal;

                if (0 !== valueEndField.length) {
                    var endValue = valueEndField.val();
                    var formattedEndVal = this.formatDate(endValue, dateFormat, this.modelDateFormat);

                    value = [formattedStartVal, formattedEndVal];
                }
            }

            this.setData({
                field: this.getField(),
                operator: operator,
                value: value
            });
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
});
