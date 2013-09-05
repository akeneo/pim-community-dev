Oro = Oro || {};

Oro.Config = Oro.Config || {};
Oro.Config.FormState = function() {
    this.initialize.apply(this, arguments);
};

_.extend(Oro.Config.FormState.prototype, {
    UNLOAD_EVENT:           'beforeunload.configFormState',
    LOAD_EVENT:             'ready.configFormState',
    CONFIRMATION_MESSAGE:   _.__('You have unsaved changes, are you sure that you want to leave?'),

    data: null,


    initialize: function() {
        Oro.Events.once('hash_navigation_request:start', _.bind(this._onDestroyHandler, this));

        var collectHandler = _.bind(this._collectHandler, this);
        $(window).on(this.LOAD_EVENT, collectHandler);
        Oro.Events.once('hash_navigation_request:refresh', collectHandler);

        $(window).on(this.UNLOAD_EVENT, _.bind(function() {
            if (this.isChanged()) {
                return this.CONFIRMATION_MESSAGE;
            }
        }, this));
        Oro.Events.on('hash_navigation_click', _.bind(this._confirmHashChange, this));
    },

    /**
     * Check is form changed
     *
     * @returns {boolean}
     */
    isChanged: function() {
        if (!_.isNull(this.data)) {
            return this.data != this.getState();
        }

        return false;
    },

    /**
     * Collect form state
     *
     * @returns {*}
     */
    getState: function() {
        var form = $('.system-configuration-container form:first');

        if (form.length) {
            return JSON.stringify(form.serialize());
        }

        return false;
    },

    /**
     * Hash change event handler
     *
     * @param event
     * @private
     */
    _confirmHashChange: function(event) {
        if (this.isChanged()) {
            event.stoppedProcess = !confirm(this.CONFIRMATION_MESSAGE);
        }
    },

    /**
     * Collecting event handler
     *
     * @private
     */
    _collectHandler: function() {
        this.data = this.getState();
    },

    /**
     * Destroys event handlers
     *
     * @private
     */
    _onDestroyHandler: function() {
        if (_.isNull(this.data)) {
            // data was not collected disable listener
            Oro.Events.off('hash_navigation_request:refresh', _.bind(this._collectHandler, this));
        } else {
            this.data = null;
        }
        Oro.Events.off('hash_navigation_click', _.bind(this._confirmHashChange, this));
        $(window).off(this.UNLOAD_EVENT);
        $(document).off(this.LOAD_EVENT);
    }
});
