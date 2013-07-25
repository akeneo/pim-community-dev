Oro = Oro || {};
Oro.EmailTemplatesUpdater = Oro.EmailTemplatesUpdater || {};

Oro.EmailTemplatesUpdater.View = Backbone.View.extend({
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
        this.showSelect = options.showSelect;
        this.template = $('#emailtemplate-chooser-template').html();
        this.target = options.target;

        this.listenTo(this.collection, 'reset', this.render);
    },

    /**
     * Trigger change event
     */
    sync: function () {
        if (this.target.val() == '' && $(this.el).val() != '') {
            $(this.el).trigger('change');
        }
    },

    /**
     * onChange event listener
     *
     * @param e {Object}
     */
    selectionChanged: function (e) {
        var entityId = $(e.currentTarget).val();
        this.collection.setEntityId(entityId);
        this.collection.fetch();
    },

    render: function() {
        $(this.target).val('');
        $(this.target).find('option[value!=""]').remove();
        $(this.target).append(_.template(this.template, {entities: this.collection.models}));
    }
});
