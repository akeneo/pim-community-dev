/* global define */
define(
    [
        'oro/translator',
        'oro/datagrid/string-cell',
        'pim/user-context'
    ],
    function (
        __,
        StringCell,
        UserContext
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
                    const locale = UserContext.get('uiLocale').replace(/_/, '-');
                    const translatedNumber = parseFloat(metricData.amount).toLocaleString(locale);

                    this.$el.html(translatedNumber + ' ' + translatedUnit);
                }

                return this;
            }
        });
    }
);
