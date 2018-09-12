/**
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(['oro/datagrid/string-cell', 'oro/translator'],
    function(StringCell, __) {
        'use strict';

        /**
         * Boolean column cell
         *
         * @extends oro.datagrid.StringCell
         */
        return StringCell.extend({
            render: function () {
                const columnValue = this.model.get(this.column.get('name'));
                const value = this.formatter.fromRaw(columnValue);
                const label = (true === value || 'true' === value || '1' === value) ? '<strong>' + __('Yes') + '</strong>' : __('No');

                this.$el.empty().html(label);

                return this;
            }
        });
    }
);
