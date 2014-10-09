define(
    ['jquery', 'oro/translator', 'oro/datagrid/html-cell'],
    function($, __, HtmlCell) {
        'use strict';

        return HtmlCell.extend({
            events: {
                'click a.toggle': 'toggle'
            },

            expandText: __('pimee_datagrid.cell.expand.expandText'),
            collapseText: __('pimee_datagrid.cell.expand.collapseText'),

            toggle: function() {
                if (this.$link.hasClass('collapsed')) {
                    this.$link.removeClass('collapsed').text(this.collapseText);
                    this.$el.html(this.formatter.fromRaw(this.model.get(this.column.get("name")))).append(this.$link);
                } else {
                    this.$link.addClass('collapsed').text(this.expandText);
                    this.$el.html(this.$link);
                }
            },

            initialize: function() {
                this.$link = $('<a href="javascript:void(0)" class="toggle collapsed">' + this.expandText + '</a>');

                return HtmlCell.prototype.initialize.apply(this, arguments);
            },

            render: function () {
                this.$el.html(this.$link);

                return this;
            }
        });
    }
);
