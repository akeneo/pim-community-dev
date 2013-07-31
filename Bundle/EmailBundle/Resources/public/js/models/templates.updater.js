Oro = Oro || {};
Oro.EmailTemplatesUpdater = Oro.EmailTemplatesUpdater || {};

Oro.EmailTemplatesUpdater.EmailTemplate = Backbone.Model.extend({
    defaults: {
        entity: '',
        id: '',
        name: ''
    }
});
