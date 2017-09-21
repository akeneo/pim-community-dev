/* global define */
define(['oro/datagrid/string-cell', 'oro/translator'],
    function(StringCell, __) {
        'use strict';

        /**
         * Complete variant product column cell
         *
         * @extends oro.datagrid.StringCell
         */
        return StringCell.extend({
            /**
             * Render the completeness.
             */
            render: function () {
                if ('product' === this.model.get('document_type')) {
                    this.$el.empty().html(__('not_available'));

                    return this;
                }

                var data = this.formatter.fromRaw(this.model.get(this.column.get('name')));
                var completeness = '-';

                if (null !== data && '' !== data) {
                    var ratio = data.complete / data.total;
                    var cssClass = '';
                    if (1 === ratio) {
                        cssClass+= 'success';
                    } else if (0 === ratio || 0 === data.total) {
                        cssClass+= 'important';
                    } else {
                        cssClass+= 'warning';
                    }

                    completeness = '<span class="AknBadge AknBadge--'+ cssClass +'">'+ data.complete+' / '+data.total +'</span>';
                }

                this.$el.empty().html(completeness);

                return this;
            }
        });
    }
);
