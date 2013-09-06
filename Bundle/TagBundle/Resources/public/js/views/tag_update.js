Oro = Oro || {};
Oro.Tags = Oro.Tags || {};

Oro.Tags.TagsUpdateView = Oro.Tags.TagView.extend({
    /** @property */
    tagsOverlayTemplate: _.template(
        '<div class="controls">' +
            '<div class="well well-small">' +
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
     * @param {String} options.autocompleteFieldId DomElement ID of autocomplete widget
     * @param {String} options.fieldId DomElement ID of hidden field with all tags
     * @param {String} options.ownFieldId DomElement ID of hidden field with own tags
     * @throws {TypeError} If mandatory options are undefined
     */
    initialize: function(options) {
        options = options || {};

        if (!options.autocompleteFieldId) {
            throw new TypeError("'autocompleteFieldId' is required")
        }

        if (!options.fieldId) {
            throw new TypeError("'fieldId' is required")
        }

        if (!options.ownFieldId) {
            throw new TypeError("'ownFieldId' is required")
        }

        Oro.Tags.TagView.prototype.initialize.apply(this, arguments);


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
        Oro.Tags.TagView.prototype.render.apply(this, arguments);

        var renderButtons = false;
        switch (true) {
            case this.options.filter == 'owner' && this.options.unassign:
                renderButtons = true;
                break;
            case this.options.filter != 'owner' && this.options.unassignGlobal:
                renderButtons = true;
                break;
        }
        if (renderButtons) {
            $(this.options.tagsOverlayId).find('span.label').each(function(i, el) {
                var $el = $(el);

                $el.append($('<span class="select2-search-choice-close"></span>'));
                $el.addClass('with-button');
            });

            $(this.options.tagsOverlayId).find('span.label .select2-search-choice-close').click(_.bind(this._removeItem, this));
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

        var $el = $(e.currentTarget).parents('li');

        var id = $($el).data('id');
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
        try {
            var allTags = $.parseJSON($(this.options.fieldId).val());
            if (! _.isArray(allTags)) {
                throw new TypeError("tags hidden field data is not array")
            }
        } catch (e) {
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
