Oro = Oro || {};
Oro.EmailTemplatesUpdater = Oro.EmailTemplatesUpdater || {};

Oro.EmailTemplatesUpdater.View = Backbone.View.extend({
    events: {
        'change': 'selectionChanged'
    },

    /**
     * Constructor
     *
     * @param options {Object}
     */
    initialize: function (options) {
        this.showSelect = options.showSelect;
        this.template = $('#emailtemplate-chooser-template').html();

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
        this.el.val('').trigger('change');
        this.el.find('option[value!=""]').remove();
        this.el.append(_.template(this.template, {entities: this.collection.models}));
    }
});
