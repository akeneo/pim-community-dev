'use strict';
/**
 * Save extension to adapt messages if ownership rights are not granted
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
define(['jquery', 'underscore', 'pim/product-edit-form/save', 'oro/messenger'], function($, _, Save, messenger) {
  const NOTIFY_ON_SUCCESS = true;

  return Save.extend({
    notifyOnSuccess: NOTIFY_ON_SUCCESS,

    save: function(options = {}) {
      if (undefined !== options.notifyOnSuccess) {
        this.notifyOnSuccess = !!options.notifyOnSuccess;
      }

      return Save.prototype.save.apply(this, options);
    },

    render: function() {
      var isOwner = this.getFormData().meta.is_owner;

      if (!isOwner) {
        this.updateSuccessMessage = _.__('pimee_enrich.entity.product_draft.flash.update.success');
        this.updateFailureMessage = _.__('pimee_enrich.entity.product_draft.flash.update.fail');
      }

      return Save.prototype.render.apply(this, arguments);
    },

    postSave: function(data) {
      this.getRoot().trigger('pim_enrich:form:entity:post_save', data);

      if (this.notifyOnSuccess) {
        messenger.notify('success', this.updateSuccessMessage, {flash: this.isFlash});
      }

      /* Reset default value to be stateless */
      this.notifyOnSuccess = NOTIFY_ON_SUCCESS;
    },
  });
});
