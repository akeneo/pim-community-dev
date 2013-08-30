/* jshint devel:true */
/* global define */
define([ 'underscore', 'backbone', 'oro/translator'],
function( _, Backbone, __) {
    'use strict';

    var $ = Backbone.$;

    /**
     * @export  oro/address/view
     * @class   oro.address.View
     * @extends Backbone.View
     */
    return Backbone.View.extend({
        tagName: 'div',

        attributes: {
            'class': 'map-item'
        },

        events: {
            'click': 'activate',
            'click button:has(.icon-remove)': 'close',
            'click button:has(.icon-edit)': 'edit'
        },

        initialize: function() {
            this.template = _.template($("#template-contact-address").html());
            this.listenTo(this.model, 'destroy', this.remove);
            this.listenTo(this.model, 'change:active', this.toggleActive);
        },

        activate: function() {
            this.model.set('active', true);
        },

        toggleActive: function() {
            if (this.model.get('active')) {
                this.$el.addClass('active');
            } else {
                this.$el.removeClass('active');
            }
        },

        edit: function(e) {
            this.trigger('edit', this, this.model);
        },

        close: function() {
            if (this.model.get('primary')) {
                alert(__('Primary address can not be removed'));
            } else {
                this.model.destroy({wait: true});
            }
        },

        render: function() {
            this.$el.append(this.template(this.model.toJSON()));
            if (this.model.get('primary')) {
                this.activate();
            }
            return this;
        }
    });
});
