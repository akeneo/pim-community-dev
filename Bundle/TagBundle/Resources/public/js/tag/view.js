/* global define */
define(['jquery', 'underscore', 'backbone', 'oro/app', 'oro/translator', 'oro/tag/collection'],
function($, _, Backbone, app, __, TagCollection) {
    'use strict';

    /**
     * @export  oro/tag/view
     * @class   oro.tag.View
     * @extends Backbone.View
     */
    return Backbone.View.extend({
        options: {
            filter: null
        },
    
        /** @property */
        template: _.template(
            '<ul id="tag-list" class="inline">' +
                '<% _.each(models, function(tag, i) { %>' +
                    '<li data-id="<%= tag.get("id") %>">' +
                        '<% if (tag.get("url").length > 0) { %>' +
                            '<a href="<%= tag.get("url") %>">' +
                        '<%} %>' +
                            '<span class="label label-info"><%= tag.get("name") %></span>' +
                        '<% if (tag.get("url").length > 0) { %>' +
                            '</a>' +
                        '<%} %>' +
                    '</li>' +
                '<%}) %>' +
                '<% if (models.length == 0) { %>' +
                    __('Not tagged') +
                '<%} %>' +
            '</ul>'
        ),
    
        /**
         * Constructor
         */
        initialize: function() {
            this.collection = new TagCollection();
            this.listenTo(this.getCollection(), 'reset', this.render);
            this.listenTo(this, 'filter', this.render);
    
            // process filter action binding
            $('#tag-sort-actions a').click(_.bind(this.filter, this));
        },
    
        /**
         * Filter collection proxy
         *
         * @returns {*}
         */
        filter: function(e) {
            var $el = $(e.target);
    
            // clear all active links
            $el.parents('ul').find('a.active').removeClass('active');
            // make current filter active
            $el.addClass('active');
    
            this.options.filter = $el.data('type');
            this.trigger('filter');
    
            return this;
        },
    
        /**
         * Get collection object
         *
         * @returns {*}
         */
        getCollection: function() {
            return this.collection;
        },
    
        /**
         * Render widget
         *
         * @returns {}
         */
        render: function() {
            $('#tags-holder').html(
                this.template(this.getCollection().getFilteredCollection(this.options.filter))
            );
            // process tag click redirect
            if (app.hashNavigationEnabled()) {
                app.hashNavigationInstance.processClicks($('#tag-list a'));
            }
    
            return this;
        }
    });
});
