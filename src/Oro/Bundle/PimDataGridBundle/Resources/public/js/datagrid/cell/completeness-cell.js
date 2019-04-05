/* global define */
define(['oro/datagrid/string-cell', 'oro/translator'],
    function(StringCell, __) {
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
                if ('product_model' === this.model.get('document_type')) {
                    this.$el.empty().html(__('pim_common.not_available'));

                    return this;
                }

                var ratio = this.formatter.fromRaw(this.model.get(this.column.get('name')));

                var completeness = '-';
                if (null !== ratio && '' !== ratio) {
                    var cssClass = '';
                    if (100 === ratio) {
                        cssClass+= 'success';
                    } else if (0 === ratio) {
                        cssClass+= 'important';
                    } else {
                        cssClass+= 'warning';
                    }

                    completeness = '<span class="AknBadge AknBadge--'+ cssClass +'">'+ ratio +'%</span>';
                }

                this.$el.empty().html(completeness);

                return this;
            }
        });
    }
);
