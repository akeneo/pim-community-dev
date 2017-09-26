/* global define */
define(['oro/datagrid/string-cell'],
    function (StringCell) {
        'use strict';

        /**
         * Label column cell for products and product models
         *
         * @extends oro.datagrid.StringCell
         */
        return StringCell.extend({

            /**
             * {@inheritdoc}
             */
            className() {
                let className = 'AknGrid-bodyCell AknGrid-bodyCell--highlight';

                if (this.model.get('document_type') === 'product_model') {
                    className += ' AknGrid-bodyCell--highlightAlternative';
                }

                return className;
            }
        });
    }
);
