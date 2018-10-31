/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {NormalizedAttributeOptionsMapping} from '../model/normalized-attribute-options-mapping';
import AttributeOptionsMappingSaver = require('../saver/attribute-options-mapping');

const BaseSaverForm = require('pim/form/common/save-form');

/**
 * Attribute Options Mapping Saver Form
 * It depends of the Attribute Options Mapping Saver, which need custom calls to generate its URL.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class SaverForm extends BaseSaverForm {
  /**
   * {@inheritdoc}
   */
  public save(): JQueryPromise<any> {
    const entity = this.getFormData()  as NormalizedAttributeOptionsMapping;

    this.showLoadingMask();
    this.getRoot().trigger('pim_enrich:form:entity:pre_save');

    return AttributeOptionsMappingSaver
      .setFamilyCode(entity.family)
      .setFranklinAttributeCode(entity.franklinAttributeCode)
      .setUrl(this.config.url)
      .save(null, entity, this.config.method || 'POST')
      .then((data: any) => {
        this.postSave();
        this.setData(data);
        this.getRoot().trigger('pim_enrich:form:entity:post_fetch', data);
      })
      .fail(this.fail.bind(this))
      .always(this.hideLoadingMask.bind(this));
  }
}

export = SaverForm;
