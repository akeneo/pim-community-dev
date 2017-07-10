

import $ from 'jquery';
import _ from 'underscore';
import __ from 'oro/translator';
import BaseFilter from 'pim/filter/filter';
import Routing from 'routing';
import template from 'pim/template/filter/product/updated';
import fetcherRegistry from 'pim/fetcher-registry';
import userContext from 'pim/user-context';
import i18n from 'pim/i18n';
import initSelect2 from 'jquery.select2';
import Datepicker from 'datepicker';
import DateContext from 'pim/date-context';
import DateFormatter from 'pim/formatter/date';
export default BaseFilter.extend({
    shortname: 'updated',
    template: _.template(template),
    events: {
        'change [name="filter-operator"], [name="filter-value-updated"]': 'updateState'
    },

        /* Date widget options */
    datetimepickerOptions: {
        format: DateContext.get('date').format,
        defaultFormat: DateContext.get('date').defaultFormat,
        language: DateContext.get('language')
    },

        /* Model date format */
    modelDateFormat: 'yyyy-MM-dd HH:mm:ss',

        /**
         * Initializes configuration.
         *
         * @param config
         */
    initialize: function (config) {
        this.config = config.config;

        return BaseFilter.prototype.initialize.apply(this, arguments);
    },

        /**
         * {@inheritdoc}
         */
    configure: function () {
        this.listenTo(this.getRoot(), 'pim_enrich:form:entity:pre_update', function (data) {
            _.defaults(data, {field: this.getCode(), operator: _.first(_.values(this.config.operators))});
        }.bind(this));

        return BaseFilter.prototype.configure.apply(this, arguments);
    },

        /**
         * Returns rendered input.
         *
         * @return {String}
         */
    renderInput: function () {
        var value    = this.getValue();
        var operator = this.getOperator();

        if ('SINCE LAST JOB' !== operator && 'SINCE LAST N DAYS' !== operator) {
            value = DateFormatter.format(value, this.modelDateFormat, DateContext.get('date').format);
        }

        return this.template({
            isEditable: this.isEditable(),
            __: __,
            field: this.getField(),
            operator: operator,
            value: value,
            operatorChoices: this.config.operators
        });
    },

        /**
         * Initializes select2 and datepicker after rendering.
         */
    postRender: function () {
        this.$('[name="filter-operator"]').select2({minimumResultsForSearch: -1});

        if ('>' === this.getOperator()) {
            Datepicker
                    .init(this.$('.date-wrapper:first'), this.datetimepickerOptions)
                    .on('changeDate', this.updateState.bind(this));
        }
    },

        /**
         * {@inheritdoc}
         */
    isEmpty: function () {
        return !this.getOperator() || 'ALL' === this.getOperator();
    },

        /**
         * Updates operator and value on fields change.
         * Value is reset after operator has changed.
         */
    updateState: function () {
        this.$('.date-wrapper:first').datetimepicker('hide');

        var oldOperator = this.getOperator();
        var value       = this.$('[name="filter-value-updated"]').val();
        var operator    = this.$('[name="filter-operator"]').val();

        if (operator !== oldOperator) {
            value = '';
        }

        if ('>' === operator) {
            value = DateFormatter.format(value, DateContext.get('date').format, this.modelDateFormat);
        } else if ('SINCE LAST JOB' === operator) {
            value = this.getParentForm().getFormData().code;
        }
        if (_.isUndefined(value)) {
            value = '';
        }

        this.setData({
            field: this.getField(),
            operator: operator,
            value: value
        });

        this.render();
    }
});

