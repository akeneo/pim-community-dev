'use strict';

define([
    'pim/common/column-list-view',
    'oro/translator'
], function (
    ColumnListView,
    __
) {
    return ColumnListView.extend({
        validateSubmission: function () {
            if (0 !== this.collection.where({displayed: true}).length) {
                this.$('.alert').hide();
                this.$el.closest('.modal')
                    .find('.btn.ok:not(.btn-primary)')
                    .addClass('btn-primary')
                    .attr('disabled', false);
            } else {
                this.$('.alert')
                    .removeClass('alert-error')
                    .addClass('alert-success')
                    .text(__('pim_enrich.export.product.filter.attributes.empty'))
                    .show();
                this.$el.closest('.modal')
                    .find('.btn.ok:not(.btn-primary)')
                    .addClass('btn-primary')
                    .attr('disabled', false);
            }
        }
    });
});
