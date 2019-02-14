import {getConnectionStatus} from '../../fetcher/franklin-connection';
import ConnectionStatus from '../../model/connection-status';

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

const __ = require('oro/translator');
const Dialog = require('pim/dialog');

/**
 * Overrides the delete Confirm modal for datagrid to check Franklin subscriptions.
 * It only overrides the default behavior for attributes deletions.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
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
    entityHint: string,
  ) {
    if (entityCode !== 'attribute') {
      return this.getDefaultConfirmDialog(entityCode, callback, entityHint);
    }

    getConnectionStatus(false).then((connectionStatus: ConnectionStatus) => {
      if (connectionStatus.productSubscriptionCount > 0) {
        return Dialog.confirmDelete(
          __(
            'pim_enrich.entity.attribute.module.save.warning',
            {count: connectionStatus.productSubscriptionCount},
            connectionStatus.productSubscriptionCount,
          ),
          __('pim_enrich.entity.attribute.module.save.title'),
          callback,
          entityHint,
        );
      }

      return this.getDefaultConfirmDialog(entityCode, callback, entityHint);
    });
  }

  /**
   * Returns the default confirm modal
   *
   * @param {string} entityCode
   * @param {any}    callback
   * @param {string} entityHint
   * @return {Promise}
   */
  private static getDefaultConfirmDialog(
    entityCode: string,
    callback: any,
    entityHint: string,
  ) {
    return Dialog.confirmDelete(
      __(`pim_enrich.entity.${entityCode}.module.delete.confirm`),
      __('pim_common.confirm_deletion'),
      callback,
      entityHint,
    );
  }
}

export = DeleteConfirm;
