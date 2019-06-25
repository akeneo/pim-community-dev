/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
import AttributeMappingStatus from '../../model/attribute-mapping-status';
import AttributesMapping from '../../model/attributes-mapping';
import AttributesMappingForFamily from '../../model/attributes-mapping-for-family';

const BaseState = require('pim/form/common/state');

/**
 * State module for the attribute mapping screen.
 */
class State extends BaseState {
  public configure() {
    this.listenTo(this.getRoot(), 'franklin_attribute_deactivated', this.setAttributeMappingStatusAsInactive);

    return super.configure();
  }

  /**
   * {@inheritdoc}
   */
  public hasModelChanged(): boolean {
    const model: AttributesMappingForFamily = this.getFormData();

    if (this.state !== JSON.stringify(model)) {
      return true;
    }

    return this.checkAttributeWithPerfectMatch(model.mapping);
  }

  /**
   * Return true when a PENDING attribute have suggested value (perfect match) and need to be saved.
   */
  private checkAttributeWithPerfectMatch(mappings: AttributesMapping) {
    for (const mapping of Object.values(mappings)) {
      if (
        mapping.status === AttributeMappingStatus.ATTRIBUTE_PENDING &&
        mapping.attribute !== null &&
        mapping.attribute !== ''
      ) {
        return true;
      }
    }
    return false;
  }

  private setAttributeMappingStatusAsInactive(franklinAttributeCode: string) {
    const model = JSON.parse(this.state) as AttributesMappingForFamily;

    model.mapping[franklinAttributeCode].status = AttributeMappingStatus.ATTRIBUTE_INACTIVE;

    (this.state as string) = JSON.stringify(model);
  }
}

export = State;
