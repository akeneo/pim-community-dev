/* global define */
define(['oro/datagrid/string-cell'],
    function(StringCell) {
        'use strict';

        /**
         * Completeness column cell
         *
         * @extends oro.datagrid.StringCell
         */
        return StringCell.extend({
            /**
             * Render the completeness.
             */
            render: function () {
                var ratio = this.formatter.fromRaw(this.model.get(this.column.get("name")));

                if (null === ratio || '' === ratio) {
                    var completeness = '-';
                } else {
                    var cssClass = '';
                    if (100 == ratio) {
                        cssClass+= 'success';
                    } else if (0 == ratio) {
                        cssClass+= 'important';
                    } else {
                        cssClass+= 'warning';
                    }

                    var completeness = '<span class="AknBadge AknBadge--'+ cssClass +'">'+ ratio +'%</span>';
                }

                this.$el.empty().html(completeness);

                return this;
            }
        });
    }
);
