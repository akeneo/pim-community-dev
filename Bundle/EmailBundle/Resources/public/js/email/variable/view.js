/* global define */
define(['jquery', 'underscore', 'backbone'],
function($, _, Backbone) {
    'use strict';

    /**
     * @export  oro/email/variable/view
     * @class   oro.email.variable.View
     * @extends Backbone.View
     */
    return Backbone.View.extend({
        events: {
            'click ul li a': 'addVariable'
        },
        target: null,
        lastElement: null,

        /**
         * Constructor
         *
         * @param options {Object}
         */
        initialize: function (options) {
            this.target = options.target;

            this.listenTo(this.model, 'sync', this.render);
            this.target.on('change', _.bind(this.selectionChanged, this));

            $('input[name*="subject"], textarea[name*="content"]')
                .on('blur', _.bind(this._updateElementsMetaData, this));
            this.render();
        },

        /**
         * onChange event listener
         *
         * @param e {Object}
         */
        selectionChanged: function (e) {
            var entityName = $(e.currentTarget).val();
            this.model.set('entityName', entityName.split('\\').join('_'));
            this.model.fetch();
        },

        /**
         * Renders target element
         *
         * @returns {*}
         */
        render: function() {
            var html = _.template(this.options.template.html(), {
                userVars: this.model.get('user'),
                entityVars: this.model.get('entity')
            });

            $(this.el).html(html);

            return this;
        },

        /**
         * Add variable to last element
         *
         * @param e
         * @returns {*}
         */
        addVariable: function(e) {
            if (!_.isNull(this.lastElement) && this.lastElement.is(':visible')) {
                this.lastElement.val(this.lastElement.val() + $(e.currentTarget).html());
            }

            return this;
        },

        /**
         * Update elements metadata
         *
         * @param e
         * @private
         * @returns {*}
         */
        _updateElementsMetaData: function(e) {
            this.lastElement = $(e.currentTarget);

            return this;
        }
    });
});
