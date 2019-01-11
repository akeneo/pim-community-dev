/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as JQuery from 'jquery';

const Routing = require('routing');

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */

/**
 * Returns the current configuration used to connect to Franklin.
 */
export function getConfiguration(): JQueryPromise<any> {
  const url = Routing.generate('akeneo_franklin_insights_connection_get');

  return JQuery.get(url);
}

/**
 * Gets the status of the connection to Franklin.
 */
export function getConnectionStatus(checkValidity: boolean): JQueryPromise<any> {
  const url = Routing.generate('akeneo_franklin_insights_connection_status_get', { checkValidity });

  return JQuery.get(url);
}
