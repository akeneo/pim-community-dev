/* global define */
define(['jquery', 'underscore', 'backbone'],
function($, _, Backbone) {
    'use strict';

    /**
     * @export  oro/email/template/view
     * @class   oro.email.template.View
     * @extends Backbone.View
     */
    return Backbone.View.extend({
        events: {
            'change': 'selectionChanged'
        },
        target: null,

        /**
         * Constructor
         *
         * @param options {Object}
         */
        initialize: function (options) {
            this.template = $('#emailtemplate-chooser-template').html();
            this.target = options.target;

            this.listenTo(this.collection, 'reset', this.render);
        },

        /**
         * onChange event listener
         *
         * @param e {Object}
         */
        selectionChanged: function (e) {
            var entityId = $(e.currentTarget).val();
            this.collection.setEntityId(entityId.split('\\').join('_'));
            this.collection.fetch();
        },

        render: function() {
            $(this.target).val('').trigger('change');
            $(this.target).find('option[value!=""]').remove();
            if (this.collection.models.length > 0) {
                $(this.target).append(_.template(this.template, {entities: this.collection.models}));
            }
        }
    });
});
