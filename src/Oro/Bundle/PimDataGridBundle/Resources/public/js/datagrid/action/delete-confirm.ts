const __ = require('oro/translator');
const Dialog = require('pim/dialog');

/**
 * Delete Confirm modal for datagrid
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class DeleteConfirm {
  /**
   * Returns a confirm modal
   *
   * @param {string} entityCode
   * @param {any}    callback
   * @param {string} entityHint
   * @return {Promise}
   */
  public static getConfirmDialog(
    entityCode: string,
    callback: any,
    entityHint: string
  ) {
    return Dialog.confirmDelete(
        __(`pim_enrich.entity.${entityCode}.module.delete.confirm`),
        __('pim_common.confirm_deletion'),
        callback,
        entityHint
    );
  }
}

export = DeleteConfirm;
