/* jshint devel:true */
/* global define */
define(['underscore', 'backbone', 'oro/dialog-widget', 'oro/widget-manager'],
function(_, Backbone, DialogWidget, WidgetManager) {
    'use strict';

    /**
     * @export  oro/multiple-entity
     * @class   oro.multipleEntity
     * @extends Backbone.View
     */
    return Backbone.View.extend({
        attributes: {
            'class': 'entity-item clearfix'
        },

        events: {
            'click .remove-btn': 'remove',
            'click a': 'viewDetails',
            'change .default-selector': 'defaultSelected'
        },

        options: {
            defaultElementName: 'default',
            model: null,
            template:
                '<a href="<%= link %>"><%= label %></a>' +
                '<div class="pull-right">' +
                    '<div class="input-prepend"><span class="add-on">' +
                    '<input type="radio" class="default-selector" name="<%= defaultElementName %>" title="<%= _.__("Default") %>" value="<%= id %>" <% if(isDefault) { %>checked="checked"<% } %>  />' +
                    '</span></div>' +
                    '<button class="btn remove-btn" title="<%= _.__("Remove") %>"><i class="icon-remove"></i></button>' +
                '</div>'
        },

        initialize: function() {
            this.template = _.template(this.options.template);
            this.listenTo(this.model, 'change:isDefault', this.toggleDefault);
        },

        /**
         * Display information about selected contact.
         *
         * @param {jQuery.Event} e
         */
        viewDetails: function(e) {
            e.stopImmediatePropagation();
            e.preventDefault();
            var widget = new DialogWidget({
                'url': this.options.model.get('link'),
                'title': this.options.model.get('label'),
                dialogOptions: {
                    'allowMinimize': true,
                    'width': 675,
                    'autoResize':true
                }
            });
            WidgetManager.addWidgetInstance(widget);
            widget.render();
        },

        defaultSelected: function(e) {
            this.options.model.set('isDefault', e.target.checked);
        },

        toggleDefault: function() {
            this.$el.find('.remove-btn')[0].disabled = this.model.get('isDefault');
        },

        render: function() {
            var data = this.model.toJSON();
            data['defaultElementName'] = this.options.defaultElementName;
            this.$el.append(this.template(data));
            this.toggleDefault();
            return this;
        }
    });
});
