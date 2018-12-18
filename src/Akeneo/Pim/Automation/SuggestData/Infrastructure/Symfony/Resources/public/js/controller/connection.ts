/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
import Locale from '../model/locale';

const BaseIndexController = require('pim/controller/common/index');
const FetcherRegistry = require('pim/fetcher-registry');
const FormBuilder = require('pim/form-builder');

/**
 * This controller loads the screen allowing the user to enter a Franklin token.
 * The token field will be available only if at least one english locale is activated.
 * If not, a message will warn the user and prevent him/her to go further.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ConnectionController extends BaseIndexController {
  /**
   * {@inheritdoc}
   */
  public renderForm(): object {
    return FetcherRegistry.getFetcher('locale').fetchActivated().then((locales: Locale[]) => {
      const formToBuild = this.containsAnEnglishLocale(locales)
        ? 'akeneo-suggest-data-' + this.options.config.entity + '-index'
        : 'akeneo-suggest-data-' + this.options.config.entity + '-index-no-active-english-locale';

      return FormBuilder.build(formToBuild)
        .then((form: any) => {
          this.on('pim:controller:can-leave', (event: any) => {
            form.trigger('pim_enrich:form:can-leave', event);
          });
          form.setElement(this.$el).render();

          return form;
        });
    });
  }

  /**
   * @param {Locale[]} locales
   *
   * @return {boolean}
   */
  private containsAnEnglishLocale(locales: Locale[]): boolean {
    return locales.some((locale: Locale) => {
      return /en_.*/.test(locale.code);
    });
  }
}

export = ConnectionController;
