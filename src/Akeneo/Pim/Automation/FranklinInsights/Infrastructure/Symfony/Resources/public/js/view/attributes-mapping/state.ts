/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
import AttributeMappingStatus from "../../model/attribute-mapping-status";

const BaseState = require('pim/form/common/state');

/**
 * State module for the mapping screen.
 * The goal of this module is to not detect state as changed if the value is changed from null to ''.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class State extends BaseState {
  /**
   * {@inheritdoc}
   */
  public hasModelChanged(): boolean {
    if (this.state !== JSON.stringify(this.getFormData())) {
      return true;
    }

    const formData = this.getFormData();
    for (let property in formData.mapping) {
      const attributeMapping = formData.mapping[property];
      if (attributeMapping.status === AttributeMappingStatus.ATTRIBUTE_PENDING && attributeMapping.attribute !== null) {
        return true;
      }
    }

    return false;
  }
}

export = State;
