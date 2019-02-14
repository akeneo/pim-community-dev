import {getConnectionStatus} from '../fetcher/franklin-connection';
import ConnectionStatus from '../model/connection-status';

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

const __ = require('oro/translator');
const BaseAttributeDelete = require('pim/attribute-edit-form/delete');
const Dialog = require('pim/dialog');

/**
 * Delete extension for attribute.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class AttributeDelete extends BaseAttributeDelete {
  /**
   * {@inheritdoc}
   */
  public delete() {
    getConnectionStatus(false).then((connectionStatus: ConnectionStatus) => {
      if (connectionStatus.productSubscriptionCount > 0) {
        return Dialog.confirmDelete(
          __(
            'pim_enrich.entity.attribute.module.save.warning',
            {count: connectionStatus.productSubscriptionCount},
            connectionStatus.productSubscriptionCount,
          ),
          __('pim_enrich.entity.attribute.module.save.title'),
          this.doDelete.bind(this),
          __(this.config.trans.subTitle),
          __(this.config.trans.buttonText),
        );
      } else {
        BaseAttributeDelete.prototype.delete.apply(this);
      }
    });
  }
}

export = AttributeDelete;
