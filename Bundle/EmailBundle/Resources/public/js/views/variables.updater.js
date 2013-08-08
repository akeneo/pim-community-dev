Oro = Oro || {};
Oro.Email = Oro.Email || {};
Oro.Email.VariablesUpdater = Oro.Email.VariablesUpdater || {};

Oro.Email.VariablesUpdater.View = Backbone.View.extend({
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

        $('input[name*="subject"], textarea[name*="content"]').on('blur', _.bind(this._updateElementsMetaData, this));
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
     */
    render: function() {
        var html = _.template(this.options.template.html(), {
            userVars: this.model.get('user'),
            entityVars: this.model.get('entity')
        });

        $(this.el).html(html);
    },

    /**
     * Add variable to last element
     *
     * @param e
     */
    addVariable: function(e) {
        if (_.isNull(this.lastElement)) {
            return false;
        }
        this.lastElement.val(this.lastElement.val() + $(e.currentTarget).html());
    },

    /**
     * Update elements metadata
     *
     * @param e
     * @private
     */
    _updateElementsMetaData: function(e) {
        this.lastElement = $(e.currentTarget);
    }
});
