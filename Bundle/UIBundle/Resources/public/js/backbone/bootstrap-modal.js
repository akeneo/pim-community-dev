var Oro = Oro || {};

/**
 * Oro extension of Bootstrap Modal wrapper for use with Backbone.
 */
(function($, _, Backbone) {
    /**
     * Implementation of Bootstrap Modal
     *
     * @class   Oro.BootstrapModal
     * @extends Backbone.BootstrapModal
     */
    Oro.BootstrapModal = Backbone.BootstrapModal.extend({
        /** @property {String} */
        className: 'modal oro-modal-danger',

        /** @property {String} */
        okButtonClass: 'btn-danger',

        /** @property {Object} */
        template: _.template('\
            <% if (title) { %>\
              <div class="modal-header">\
                <% if (allowCancel) { %>\
                  <a class="close">×</a>\
                <% } %>\
                <h3><%- title %></h3>\
              </div>\
            <% } %>\
            <div class="modal-body"><%= content %></div>\
            <div class="modal-footer">\
              <a href="#" class="btn ok <%= okButtonClass %>"><%- okText %></a>\
              <% if (allowCancel && cancelText) { %>\
                <a href="#" class="btn cancel"><%- cancelText %></a>\
              <% } %>\
            </div>\
        '),

        /**
         * @param {Object} options
         */
        initialize: function(options) {
            options = _.extend({
                cancelText: _.__('Cancel')
            }, options);

            if (!options.okButtonClass) {
                options.okButtonClass = this.okButtonClass;
            }
            options = _.extend({
                template: this.template,
                className: this.className
            }, options);

            Backbone.BootstrapModal.prototype.initialize.apply(this, arguments);
        },

        open: function() {
            Backbone.BootstrapModal.prototype.open.apply(this, arguments);
            
            this.once('cancel', _.bind(function() {
                this.$el.trigger('hidden');
            }, this));
        }
    });
})(jQuery, _, Backbone);
