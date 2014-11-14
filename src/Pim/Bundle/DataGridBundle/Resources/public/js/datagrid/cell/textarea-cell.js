/* global define */
define(['oro/datagrid/string-cell', 'bootstrap'],
function(StringCell) {
    'use strict';

    return StringCell.extend({
        render: function() {
            this.$el.empty();
            this.$el.html('<span id="' + this.model.get("sku") + '">' + this.formatter.fromRaw(this.model.get(this.column.get("name"))).text.substring(0, 40) + ' ... </span>');
            this.delegateEvents();

            this.$el.popover({
                title: this.formatter.fromRaw(this.column.get('label')),
                content: this.formatter.fromRaw(this.model.get(this.column.get('name'))).text,
                delay: {
                    show: 500,
                    hide: 100
                },
                selector: '#' + this.model.get("sku"),
                trigger: 'hover'
            });

            return this;
        }
    });
});
