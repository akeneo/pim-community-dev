import Saver from 'akeneoreferenceentity/domain/saver/saver';
import ReferenceEntity from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import {postJSON} from 'akeneoreferenceentity/tools/fetch';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import handleError from 'akeneoreferenceentity/infrastructure/tools/error-handler';

const routing = require('routing');

export interface ReferenceEntitySaver extends Saver<ReferenceEntity> {}

export class ReferenceEntitySaverImplementation implements ReferenceEntitySaver {
  constructor() {
    Object.freeze(this);
  }

  async save(referenceEntity: ReferenceEntity): Promise<ValidationError[] | null> {
    return await postJSON(
      routing.generate('akeneo_reference_entities_reference_entity_edit_rest', {
        identifier: referenceEntity.getIdentifier().stringValue(),
      }),
      referenceEntity.normalize()
    ).catch(handleError);
  }

  async create(referenceEntity: ReferenceEntity): Promise<ValidationError[] | null> {
    return await postJSON(
      routing.generate('akeneo_reference_entities_reference_entity_create_rest'),
      referenceEntity.normalize()
    ).catch(handleError);
  }
}

export default new ReferenceEntitySaverImplementation();
