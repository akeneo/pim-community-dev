/* global define */
define(['underscore', 'backbone', 'oro/translator', 'backbone/bootstrap-modal'],
function(_, Backbone, __) {
    'use strict';

    /**
     * Implementation of Bootstrap Modal
     * Oro extension of Bootstrap Modal wrapper for use with Backbone.
     *
     * @export  oro/modal
     * @class   oro.Modal
     * @extends Backbone.BootstrapModal
     */
    return Backbone.BootstrapModal.extend({
        /** @property {String} */
        className: 'modal oro-modal-danger',

        open: function() {
            Backbone.BootstrapModal.prototype.open.apply(this, arguments);

            this.once('cancel', _.bind(function() {
                this.$el.trigger('hidden');
            }, this));
        }
    });
});
