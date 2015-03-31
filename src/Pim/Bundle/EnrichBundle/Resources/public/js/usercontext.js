'use strict';

define(
    ['underscore', 'backbone', 'oro/mediator'],
    function(_, Backbone, mediator) {
        return {
            userContext: null,
            model: null,
            setUserContext: function(userContext) {
                if (null === this.model) {
                    this.model = new Backbone.Model({});
                }

                this.model.set(userContext);
            },
            getUserContext: function() {
                if (!this.model) {
                    throw new Error('User context has to be set');
                }

                return this.model;
            },
            setCatalogLocale: function(catalogLocale) {
                this.model.set('catalogLocale', catalogLocale);

                mediator.trigger('usercontext:catalog_locale:changed');
            },
            setCatalogChannel: function(catalogChannel) {
                this.model.set('catalogChannel', catalogChannel);

                mediator.trigger('usercontext:catalog_channel:changed');
            },
            setUserLocale: function(userLocale) {
                this.model.set('userLocale', userLocale);

                mediator.trigger('usercontext:user_locale:changed');
            }
        };
    }
);
