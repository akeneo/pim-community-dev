import ReferenceEntity from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import {postJSON} from 'akeneoreferenceentity/tools/fetch';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import handleError from 'akeneoreferenceentity/infrastructure/tools/error-handler';
import ReferenceEntityCreation from 'akeneoreferenceentity/domain/model/reference-entity/creation';

const routing = require('routing');

export interface ReferenceEntitySaver {
  save: (entity: ReferenceEntity) => Promise<ValidationError[] | null>;
  create: (entity: ReferenceEntityCreation) => Promise<ValidationError[] | null>;
}

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

  async create(referenceEntityCreation: ReferenceEntityCreation): Promise<ValidationError[] | null> {
    return await postJSON(
      routing.generate('akeneo_reference_entities_reference_entity_create_rest'),
      referenceEntityCreation.normalize()
    ).catch(handleError);
  }
}

export default new ReferenceEntitySaverImplementation();
