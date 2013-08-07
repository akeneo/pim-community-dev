Oro = Oro || {};
Oro.Email = Oro.Email || {};
Oro.Email.VariablesUpdater = Oro.Email.VariablesUpdater || {};

Oro.Email.VariablesUpdater.View = Backbone.View.extend({
    events: {
        'click ul li a': 'addVariable'
    },
    target: null,

    /**
     * Constructor
     *
     * @param options {Object}
     */
    initialize: function (options) {
        this.target = options.target;

        this.listenTo(this.model, 'sync', this.render);
        this.target.on('change', _.bind(this.selectionChanged, this));

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

    addVariable: function(e) {
        console.log(e);
    }
});
