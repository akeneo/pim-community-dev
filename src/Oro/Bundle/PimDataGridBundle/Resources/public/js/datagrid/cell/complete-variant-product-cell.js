/**
 * Displays the number of complete variant product model for a product model, eg: 2 / 10.
 *
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
                if ('product_model' !== this.model.get('document_type')) {
                    this.$el.empty().html(__('pim_common.not_available'));

                    return this;
                }

                const data = this.formatter.fromRaw(this.model.get(this.column.get('name')));
                let completeness = '-';

                if (null !== data && '' !== data) {
                    let ratio = data.complete / data.total;
                    let cssClass = '';
                    if (1 === ratio) {
                        cssClass += 'success';
                    } else if (0 === ratio || 0 === data.total) {
                        cssClass += 'important';
                    } else {
                        cssClass += 'warning';
                    }

                    completeness = '<span class="AknBadge AknBadge--'+cssClass+'">'+data.complete+' / '+data.total +'</span>';
                }

                this.$el.empty().html(completeness);

                return this;
            }
        });
    }
);
