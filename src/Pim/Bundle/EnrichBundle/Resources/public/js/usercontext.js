'use strict';

define(
    ['underscore', 'oro/mediator'],
    function(_, mediator) {
        return {
            userContext: null,
            setUserContext: function(userContext) {
                this.userContext = userContext;
            },
            getUserContext: function() {
                if (!this.userContext) {
                    throw new Error('User context has to be set');
                }

                return this.userContext;
            },
            setCatalogLocale: function(catalogLocale) {
                this.getUserContext().catalogLocale = catalogLocale;

                mediator.trigger('usercontext:catalog_locale:changed');
            },
            setCatalogChannel: function(catalogChannel) {
                this.getUserContext().catalogChannel = catalogChannel;

                mediator.trigger('usercontext:catalog_channel:changed');
            },
            setUserLocale: function(userLocale) {
                this.getUserContext().userLocale = userLocale;

                mediator.trigger('usercontext:user_locale:changed');
            }
        };
    }
);
