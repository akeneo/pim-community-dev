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
                let className = 'AknGrid-bodyCell AknGrid-bodyCell--noWrap AknGrid-bodyCell--highlight';

                if (this.model.get('document_type') === 'product_model') {
                    className += ' AknGrid-bodyCell--highlightAlternative';
                }

                return className;
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                StringCell.prototype.render.apply(this, arguments);
                const columnValue = this.model.get(this.column.get('name'));
                this.$el.attr('title', columnValue);

                return this;
            }
        });
    }
);
