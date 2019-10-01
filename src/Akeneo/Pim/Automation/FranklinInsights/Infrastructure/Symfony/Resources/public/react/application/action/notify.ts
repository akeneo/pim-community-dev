/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {NotificationLevel} from '../notification-level';

export const NOTIFY = 'NOTIFY';

export interface NotifyAction {
  type: typeof NOTIFY;
  level: NotificationLevel;
  message: string;
}

export function notify(level: NotificationLevel, message: string): NotifyAction {
  return {
    type: NOTIFY,
    level,
    message
  };
}
