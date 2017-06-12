/* global define */
define(['oro/datagrid/string-cell', 'oro/translator'],
    function(StringCell, __) {
        'use strict';

        /**
         * Enabled column cell
         *
         * @extends oro.datagrid.StringCell
         */
        return StringCell.extend({
            /**
             * Render the field enabled.
             */
            render: function () {
                var value = this.formatter.fromRaw(this.model.get(this.column.get("name")));
                var enabled = true === value ? 'enabled' : 'disabled';

                this.$el.empty().html('<div class="AknBadge AknBadge--round AknBadge--' + enabled + ' status-' + enabled + '">' +
                    '<i class="AknBadge-icon icon-status-' + enabled + ' icon-circle"></i>' + __(enabled) + '</div>');

                return this;
            }
        });
    }
);
