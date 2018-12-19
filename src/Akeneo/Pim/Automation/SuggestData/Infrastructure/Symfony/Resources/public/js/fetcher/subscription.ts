/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

const BaseFetcher = require('pim/base-fetcher');

/**
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class SubscriptionFetcher extends BaseFetcher {
  /**
   * Count elements
   *
   * @param {Object} searchOptions
   * @return {Promise}
   */
  public count(searchOptions: any): JQueryPromise<number> {
    const url = this.options.urls.count;

    return this.getJSON(url, searchOptions).then((result: {count: number}) => {
      return result.count;
    }).promise();
  }
}

export = SubscriptionFetcher;
