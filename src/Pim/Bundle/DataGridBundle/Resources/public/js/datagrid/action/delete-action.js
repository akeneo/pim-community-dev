/* global define */
import _ from 'underscore';
import messenger from 'oro/messenger';
import __ from 'oro/translator';
import DeleteConfirmation from 'oro/delete-confirmation';
import Modal from 'oro/modal';
import ModelAction from 'oro/datagrid/model-action';
import mediator from 'oro/mediator';
import userContext from 'pim/user-context';


/**
 * Delete action with confirm dialog, triggers REST DELETE request
 *
 * @export  oro/datagrid/delete-action
 * @class   oro.datagrid.DeleteAction
 * @extends oro.datagrid.ModelAction
 */
export default ModelAction.extend({

  /** @type oro.Modal */
  errorModal: undefined,

  /** @type oro.Modal */
  confirmModal: undefined,

  /**
   * Initialize view
   *
   * @param {Object} options
   * @param {Backbone.Model} options.model Optional parameter
   * @throws {TypeError} If model is undefined
   */
  initialize: function(options) {
    options = options || {};

    this.gridName = options.datagrid.name;

    ModelAction.prototype.initialize.apply(this, arguments);
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
    this.model.id = true;
    this.model.destroy({
      url: this.getLink(),
      wait: true,
      error: function() {
        this.getErrorDialog().open();
      }.bind(this),
      success: function() {
        var messageText = __('flash.' + this.getEntityHint() + '.removed');
        messenger.notify('success', messageText);
        userContext.fetch();

        mediator.trigger('datagrid:doRefresh:' + this.gridName);
      }.bind(this)
    });
  },

  /**
   * Get view for confirm modal
   *
   * @return {oro.Modal}
   */
  getConfirmDialog: function() {
    if (!this.confirmModal) {
      this.confirmModal = new DeleteConfirmation({
        content: __('confirmation.remove.' + this.getEntityHint())
      });
      this.confirmModal.on('ok', _.bind(this.doDelete, this));
    }
    return this.confirmModal;
  },

  /**
   * Get view for error modal
   *
   * @return {oro.Modal}
   */
  getErrorDialog: function() {
    if (!this.errorModal) {
      this.errorModal = new Modal({
        title: __('Delete Error'),
        content: __('error.removing.' + this.getEntityHint()),
        cancelText: false
      });
    }
    return this.errorModal;
  },

  getEntityHint: function() {
    return this.datagrid && this.datagrid.entityHint ? this.datagrid.entityHint : 'item';
  }
});

