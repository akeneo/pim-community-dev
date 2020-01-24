/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as i18n from 'pimui/js/i18n';
import {BackendAssetFamily} from "../model/asset-family";
const BaseSelect = require('pim/form/common/fields/simple-select-async');
const UserContext = require("pim/user-context");
const Property = require('pim/common/property');

class FamilySelector extends BaseSelect {
  /**
   * {@inheritdoc}
   */
  select2Results(response: {total: number, items: BackendAssetFamily[]}) {
    const nbResults = response.total;
    const more = this.resultsPerPage === nbResults;

    return {
      more: more,
      results: response.items.map(item => this.convertBackendItem(item)),
    };
  }

  /**
   * {@inheritdoc}
   */
  public convertBackendItem(item: BackendAssetFamily) {
    return {
      id: item.identifier,
      text: i18n.getLabel(<{[locale: string]: string}> item.labels, UserContext.get('catalogLocale'), item.identifier),
      // text: item.labels[UserContext.get('catalogLocale')] || '[' + item.identifier + ']',
    }
  }

  /**
   * {@inheritdoc}
   */
  protected select2InitSelection(element: any, callback: any) {
    const id = $(element).val();
    if ('' !== id) {
      $.ajax({
        url: this.choiceUrl,
        data: {options: {identifiers: [id]}},
        type: this.choiceVerb,
      }).then(response => {
        let selected: BackendAssetFamily = response.items.find((item: BackendAssetFamily) => {
          return item.identifier === id;
        });
        if (undefined === selected) {
          return;
        }
        callback(this.convertBackendItem(selected));
      });
    }
  }

  /**
   * {@inheritdoc}
   */
  protected getFieldErrors(errors: any) {
    const error = Property.accessProperty(errors, this.fieldName, null);
    if (error === null) {
      return [];
    } else {
      return [{message: error}];
    }
  }
}

export = FamilySelector;
