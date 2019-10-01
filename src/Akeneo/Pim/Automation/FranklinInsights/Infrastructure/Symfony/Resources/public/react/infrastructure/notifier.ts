/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

const messenger = require('oro/messenger');

export const notify = (level: 'info' | 'success' | 'warning' | 'error', message: string) =>
  messenger.notify(level, message);
