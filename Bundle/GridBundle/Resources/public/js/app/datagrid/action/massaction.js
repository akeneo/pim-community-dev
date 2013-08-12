var Oro = Oro || {};
Oro.Datagrid = Oro.Datagrid || {};
Oro.Datagrid.Action = Oro.Datagrid.Action || {};

/**
 * Basic mass action class.
 *
 * @class   Oro.Datagrid.MassAction
 * @extends Oro.Datagrid.Action.AbstractAction
 */
Oro.Datagrid.Action.MassAction = Oro.Datagrid.Action.AbstractAction.extend({
    /** @property {Oro.Datagrid.Grid} */
    datagrid: null,

    /** @property Backbone.BootstrapModal */
    errorModal: undefined,

    /** @property Backbone.BootstrapModal */
    confirmModal: undefined,

    messages: {
        confirm_title: _.__('Mass Action Confirmation'),
        confirm_content: _.__('Are you sure you want to do this?'),
        confirm_ok: _.__('Yes, do it'),
        error_title: _.__('Mass Action Error'),
        error_content: _.__('Cannot perform mass action.')
    },

    /**
     * Initialize view
     *
     * @param {Object} options
     * @param {Oro.Datagrid.Grid} options.datagrid
     * @throws {TypeError} If datagrid is undefined
     */
    initialize: function(options) {
        options = options || {};

        if (!options.datagrid) {
            throw new TypeError("'datagrid' is required");
        }
        this.datagrid = options.datagrid;

        Oro.Datagrid.Action.AbstractAction.prototype.initialize.apply(this, arguments);
    },

    /**
     * Execute delete model
     */
    execute: function() {
        this.getConfirmDialog().open();
    },

    /**
     * Confirm delete item
     */
    doDelete: function() {
        // TODO Do delete
    },

    /**
     * Get view for confirm modal
     *
     * @return {Oro.BootstrapModal}
     */
    getConfirmDialog: function() {
        if (!this.confirmModal) {
            // TODO Replace translations with passed from configuration of mass action
            this.confirmModal = new Oro.BootstrapModal({
                title: this.messages.confirm_title,
                content: this.messages.confirm_content,
                okText: this.messages.confirm_ok,
                allowCancel: 'false'
            });
            this.confirmModal.on('ok', _.bind(this.doDelete, this));
        }
        return this.confirmModal;
    },

    /**
     * Get view for error modal
     *
     * @return {Oro.BootstrapModal}
     */
    getErrorDialog: function() {
        if (!this.errorModal) {
            this.confirmModal = new Oro.BootstrapModal({
                title: this.messages.error_title,
                content: this.messages.error_content,
                cancelText: false
            });
        }
        return this.confirmModal;
    }
});
