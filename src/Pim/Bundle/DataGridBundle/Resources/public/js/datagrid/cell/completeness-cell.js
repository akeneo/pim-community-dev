/* global define */
import StringCell from 'oro/datagrid/string-cell';
        

        /**
         * Completeness column cell
         *
         * @extends oro.datagrid.StringCell
         */
        export default StringCell.extend({
            /**
             * Render the completeness.
             */
            render: function () {
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
    
