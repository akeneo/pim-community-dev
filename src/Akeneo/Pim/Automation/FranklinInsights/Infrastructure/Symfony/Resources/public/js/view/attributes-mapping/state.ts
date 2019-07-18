/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
import AttributeMappingStatus from '../../model/attribute-mapping-status';
import AttributeMappingModel from '../../model/attributes-mapping-model';

const BaseState = require('pim/form/common/state');

/**
 * State module for the attribute mapping screen.
 */
class State extends BaseState {
  public configure() {
    this.listenTo(this.getRoot(), 'franklin_attribute_deactivated', this.setAttributeMappingStatusAsInactive);

    return super.configure();
  }

  public collectState() {
    const model: AttributeMappingModel = this.getFormData();

    this.state = this.getStateFromModel(model);
  }

  public hasModelChanged(): boolean {
    const model: AttributeMappingModel = this.getFormData();

    if (this.state !== this.getStateFromModel(model)) {
      return true;
    }

    return false;
  }

  private setAttributeMappingStatusAsInactive(franklinAttributeCode: string) {
    const model = JSON.parse(this.state) as AttributeMappingModel;

    model.mapping[franklinAttributeCode].status = AttributeMappingStatus.ATTRIBUTE_INACTIVE;
    model.mapping[franklinAttributeCode].attribute = null;

    this.state = this.getStateFromModel(model);
  }

  private getStateFromModel(model: AttributeMappingModel): string {
    const state = {
      ...model
    };
    delete state.selectedFranklinAttributes;

    return JSON.stringify(state);
  }
}

export = State;
