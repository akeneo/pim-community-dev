/* global define */
define(['jquery', 'underscore', 'oro/tag/view', 'json'],
function($, _, TagView) {
    'use strict';

    /**
     * @export  oro/tag/update-view
     * @class   oro.tag.UpdateView
     * @extends oro.tag.View
     */
    return TagView.extend({
        /** @property */
        tagsOverlayTemplate: _.template(
            '<div class="controls">' +
                '<div class="well well-small span6">' +
                    '<div id="tags-holder"></div>' +
                '</div>' +
            '</div>'
        ),

        /** @property {Object} */
        options: {
            filter: null,
            tagsOverlayId: '#tags-overlay',
            autocompleteFieldId: null,
            fieldId: null,
            ownFieldId: null,
            unassign: false,
            unassignGlobal: false
        },

        /**
         * Initialize widget
         *
         * @param {Object} options
         * @param {Backbone.Collection} options.tags
         * @param {string} options.autocompleteFieldId DomElement ID of autocomplete widget
         * @param {string} options.fieldId DomElement ID of hidden field with all tags
         * @param {string} options.ownFieldId DomElement ID of hidden field with own tags
         * @throws {TypeError} If mandatory options are undefined
         */
        initialize: function(options) {
            options = options || {};

            if (!options.autocompleteFieldId) {
                throw new TypeError("'autocompleteFieldId' is required");
            }

            if (!options.fieldId) {
                throw new TypeError("'fieldId' is required");
            }

            if (!options.ownFieldId) {
                throw new TypeError("'ownFieldId' is required");
            }

            TagView.prototype.initialize.apply(this, arguments);

            this._renderOverlay();
            this._prepareCollections();
            this.listenTo(this.getCollection(), 'add', this.render);
            this.listenTo(this.getCollection(), 'add', this._updateHiddenInputs);
            this.listenTo(this.getCollection(), 'remove', this.render);
            this.listenTo(this.getCollection(), 'remove', this._updateHiddenInputs);

            $(this.options.autocompleteFieldId).on('change', _.bind(this._addItem, this));
        },

        /**
         * Render widget
         *
         * @returns {}
         */
        render: function() {
            TagView.prototype.render.apply(this, arguments);

            if ((this.options.filter === 'owner' && this.options.unassign) ||
                (this.options.filter !== 'owner' && this.options.unassignGlobal)) {
                $(this.options.tagsOverlayId).find('span.label').each(function(i, el) {
                    var $el = $(el);

                    $el.append($('<span class="select2-search-choice-close"></span>'));
                    $el.addClass('with-button');
                });

                $(this.options.tagsOverlayId)
                    .find('span.label .select2-search-choice-close')
                    .click(_.bind(this._removeItem, this));
            }
        },

        /**
         * Add item from autocomplete to internal collection
         *
         * @param {Object} e select2.change event object
         * @private
         */
        _addItem: function(e) {
            e.preventDefault();
            var tag = e.added;

            if (!_.isUndefined(tag)) {
                this.getCollection().addItem(tag);
            }

            // clear autocomplete
            $(e.target).select2('val', '');
        },

        /**
         * Removes item
         *
         * @param e
         * @returns {*}
         * @private
         */
        _removeItem: function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            var $el = $(e.currentTarget).parents('li'),
                id = $($el).data('id');
            if (!_.isUndefined(id)) {
                this.getCollection().removeItem(id, this.options.filter);
            }
            return this;
        },

        /**
         * Render overlay block
         *
         * @returns {*}
         * @private
         */
        _renderOverlay: function() {
            $(this.options.tagsOverlayId).append(this.tagsOverlayTemplate());
            return this;
        },

        /**
         * Fill data to collections from hidden inputs
         *
         * @returns {*}
         * @private
         */
        _prepareCollections: function() {
            var allTags = $.parseJSON($(this.options.fieldId).val());
            if (!_.isArray(allTags)) {
                allTags = [];
            }

            this.getCollection().reset(allTags);
            return this;
        },

        /**
         * Update hidden inputs triggered by collection change
         *
         * @private
         */
        _updateHiddenInputs: function() {
            $(this.options.fieldId).val(JSON.stringify(this.getCollection()));
            $(this.options.ownFieldId).val(JSON.stringify(this.getCollection().getFilteredCollection('owner')));
        }
    });
});
