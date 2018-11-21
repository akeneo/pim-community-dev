import * as $ from 'jquery';
const BaseSelect = require('pim/form/common/fields/select');
const FetcherRegistry = require('pim/fetcher-registry');

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
type InterfaceNormalizedLocale = {
  code: string;
  label: string;
}

class UiLocaleField extends BaseSelect {
  /**
   * {@inheritdoc}
   */
  configure() {
    return $.when(
      BaseSelect.prototype.configure.apply(this, arguments),
      FetcherRegistry.getFetcher('ui-locale').fetchAll()
        .then((locales: InterfaceNormalizedLocale[]) => {
          this.config.choices = locales;
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

export = UiLocaleField
