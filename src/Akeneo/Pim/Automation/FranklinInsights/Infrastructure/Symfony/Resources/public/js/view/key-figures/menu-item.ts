/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {getConnectionStatus} from '../../fetcher/franklin-connection';
import ConnectionStatus from '../../model/connection-status';

const BaseItem = require('pim/menu/item');

/**
 * @author Willy MESNAGE <willy.mesnage@akeneo.com>
 */
class MenuItem extends BaseItem {
  public render(): any {
    return getConnectionStatus(false).then((connectionStatus: ConnectionStatus) => {
      return connectionStatus.isActive ? BaseItem.prototype.render.apply(this) : null;
    });
  }
}

export = MenuItem;
