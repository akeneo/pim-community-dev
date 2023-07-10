'use strict';

/**
 * This component allows to display an identifier field.
 * It is only displayed when the family contains the main identifier attribute.
 *
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(['pim/product-edit-form/creation/identifier', 'pim/fetcher-registry'], function (Identifier, FetcherRegistry) {
  return Identifier.extend({
    shouldDisplay: async function () {
      const familyCode = this.getFormData()?.family;
      if (familyCode) {
        return FetcherRegistry.getFetcher('family')
          .fetch(familyCode)
          .then(family => {
            return !!family.attributes.find(attribute => attribute.is_main_identifier);
          });
      } else {
        return new Promise(resolve => resolve(false));
      }
    },
  });
});
