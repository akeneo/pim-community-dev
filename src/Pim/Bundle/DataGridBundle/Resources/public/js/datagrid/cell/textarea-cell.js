/* global define */
define(['oro/datagrid/string-cell', 'bootstrap'],
function(StringCell) {
    'use strict';

    return StringCell.extend({
        render: function() {
            StringCell.prototype.render.apply(this, arguments);

            this.$el.popover({
                content: this.formatter.fromRaw(this.model.get(this.column.get('name'))),
                delay: {
                    show: 500,
                    hide: 100
                },
                trigger: 'hover'
            });

            return this;
        }
    });
});
