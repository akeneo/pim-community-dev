import * as $ from 'jquery';
const BaseSelect = require('pim/form/common/fields/select');
const FetcherRegistry = require('pim/fetcher-registry');
const UserContext = require('pim/user-context');

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
type InterfaceNormalizedChannel = {
  code: string;
  labels: { [key:string] : string };
}

class ChannelField extends BaseSelect {
  /**
   * {@inheritdoc}
   */
  configure() {
    return $.when(
      BaseSelect.prototype.configure.apply(this, arguments),
      FetcherRegistry.getFetcher('channel').fetchAll()
        .then((scopes: InterfaceNormalizedChannel[]) => {
          this.config.choices = scopes;
        })
    );
  }

  /**
   * @{inheritdoc}
   */
  formatChoices(scopes: InterfaceNormalizedChannel[]): { [key:string] : string } {
    return scopes.reduce((result: { [key:string] : string }, channel: InterfaceNormalizedChannel) => {
      const label = channel.labels[UserContext.get('catalogLocale')];
      result[channel.code] = label !== undefined ? label : '[' + channel.code + ']';

      return result;
    }, {});
  }
}

export = ChannelField;
