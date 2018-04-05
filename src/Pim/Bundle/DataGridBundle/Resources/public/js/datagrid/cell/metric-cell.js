/* global define */
define(
    [
        'oro/translator',
        'oro/datagrid/string-cell'
    ],
    function (
        __,
        StringCell
    ) {
        'use strict';

        return StringCell.extend({
            /**
             * Render an metric for a datagrid cell
             */
            render: function () {
                this.$el.empty();

                const metricData = this.model.get(this.column.get('name'));
                if (null !== metricData) {
                    const translatedUnit = __('pim_measure.units.' + metricData.family + '.' + metricData.unit);
                    const translatedNumber = parseFloat(metricData.amount).toLocaleString('en-US');
                    this.$el.html(translatedNumber + ' ' + translatedUnit);
                }

                return this;
            }
        });
    }
);
