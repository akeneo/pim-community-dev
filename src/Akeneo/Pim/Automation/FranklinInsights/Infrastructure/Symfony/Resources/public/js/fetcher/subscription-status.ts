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
 * Gets the subscription status of a product.
 *
 * @param {number} productId
 */
export function getSubscriptionStatus(productId: number): JQueryPromise<any> {
  const url = Routing.generate(
    'akeneo_franklin_insights_franklin_subscription_status',
    {productId},
  );

  return JQuery.get(url);
}
