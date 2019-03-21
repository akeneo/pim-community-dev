/* global define */
define(['oro/datagrid/string-cell', 'oro/translator'],
    function(StringCell, __) {
        'use strict';

        /**
         * Boolean column cell
         *
         * @extends oro.datagrid.StringCell
         */
        return StringCell.extend({
            /**
             * Render the boolean.
             */
            render: function () {
                var value = this.formatter.fromRaw(this.model.get(this.column.get("name")));
                if (null === value || '' === value) {
                    return this;
                }

                var status = (true === value || 'true' === value || '1' === value) ? 'success' : 'important';
                var label = (true === value || 'true' === value || '1' === value) ? __('pim_common.yes') : __('pim_common.no');

                this.$el.empty().html('<span class="AknBadge AknBadge--' + status +'">' + __(label) + '</span>');

                return this;
            }
        });
    }
);
