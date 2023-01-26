/* global define */
define([
  'jquery',
  'underscore',
  'oro/translator',
  'routing',
  'oro/datagrid/mass-action',
  'pim/router',
  'oro/messenger',
  'oro/loading-mask',
  'pim/dialog',
], function ($, _, __, Routing, MassAction, router, messenger, LoadingMask, Dialog) {
  'use strict';

  // @TODO RAB-1259: Adapt (and maybe rewrite in TS) this component to use the double-check modal

  /**
   * Mass attribute delete action
   *
   * @export  oro/datagrid/attribute-mass-delete-action
   * @class   oro.datagrid.AttributeMassDeleteAction
   *
   * @extends oro.datagrid.MassAction
   */
  return MassAction.extend({
    /** @property {string} */
    identifierFieldName: 'code',

    /** @type oro.Modal */
    errorModal: undefined,

    /** @type oro.Modal */
    confirmModal: undefined,

    initialize: function (options) {
      MassAction.prototype.initialize.apply(this, arguments);
    },

    /**
     * Displays a confirm dialog and mass delete if action is confirmed.
     */
    execute: function () {
      this.getData().then(data => {
        this.getConfirmDialog(data);
      });
    },

    /**
     * Converts grid data into pqb filters and gathers job instance code, actions and items count.
     *
     * @return {Promise}
     */
    getData: function () {
      let actionParameters = this.getActionParameters();
      actionParameters.actionName = this.route_parameters['actionName'];
      actionParameters.gridName = this.route_parameters['gridName'];
      const query = `?${$.param(actionParameters)}`;

      return $.ajax({
        url: Routing.generate('pim_enrich_mass_edit_rest_get_filter') + query,
        method: 'POST',
      }).then(response => {
        return {
          filters: response.filters,
          jobInstanceCode: 'delete_attributes',
          actions: [this.route_parameters['actionName']],
          itemsCount: response.itemsCount,
        };
      });
    },

    /**
     * Get view for confirm modal.
     *
     * @param {Object} data
     *
     * @return {oro.Modal}
     */
    getConfirmDialog: function (data) {
      this.confirmModal = Dialog.confirmDelete(
        __('pim_enrich.entity.attribute.module.mass_delete.confirm'),
        __('pim_common.confirm_deletion'),
        this.doMassDelete.bind(this, data),
        this.getEntityHint(true),
        'pim_common.delete'
      );

      return this.confirmModal;
    },

    /**
     * Sends request to mass delete attributes.
     *
     * @param {Object} data
     */
    doMassDelete: function (data) {
      const loadingMask = new LoadingMask();
      loadingMask.render().$el.appendTo($('.hash-loading-mask')).show();

      $.ajax({
        method: 'POST',
        contentType: 'application/json',
        url: Routing.generate('pim_enrich_mass_edit_rest_launch'),
        data: JSON.stringify(data),
      })
        .then(() => {
          router.redirectToRoute('pim_enrich_attribute_index');

          const translatedAction = __('pim_datagrid.mass_action.mass_delete');
          messenger.notify(
            'success',
            __('pim_enrich.entity.attribute.module.mass_delete.launched', {
              operation: translatedAction,
            })
          );
        })
        .fail(() => {
          messenger.notify('error', __('pim_enrich.entity.attribute.module.mass_delete.cannot_be_launched'));
        })
        .always(() => {
          loadingMask.hide().$el.remove();
        });
    },
  });
});
