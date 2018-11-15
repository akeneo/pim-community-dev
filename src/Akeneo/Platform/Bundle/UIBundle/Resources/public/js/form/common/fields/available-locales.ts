import * as $ from 'jquery';
const BaseSelect = require('pim/form/common/fields/select');
const FetcherRegistry = require('pim/fetcher-registry');

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
type InterfaceNormalizedLocale = {
  code: string;
  label: string;
}

class AvailableLocales extends BaseSelect {
  /**
   * {@inheritdoc}
   */
  configure() {
    return $.when(
      BaseSelect.prototype.configure.apply(this, arguments),
      FetcherRegistry.getFetcher('locale').fetchActivated()
        .then((availableLocales: InterfaceNormalizedLocale[]) => {
          this.config.choices = availableLocales;
        })
    );
  }

  /**
   * {@inheritdoc}
   */
  formatChoices(locales: InterfaceNormalizedLocale[]): { [key:string] : string } {
    return locales.reduce((result: { [key:string] : string }, locale: InterfaceNormalizedLocale) => {
      result[locale.code] = locale.label;

      return result;
    }, {});
  }
}

export = AvailableLocales;
