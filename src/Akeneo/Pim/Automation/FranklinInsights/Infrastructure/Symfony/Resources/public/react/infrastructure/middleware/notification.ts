/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {NOTIFY} from '../../application/action/notify';
import {notify} from '../notifier';

export const notificationMiddleware = () => (next: any) => (action: any) => {
  if (action.type === NOTIFY) {
    notify(action.level, action.message);
  }

  return next(action);
};
