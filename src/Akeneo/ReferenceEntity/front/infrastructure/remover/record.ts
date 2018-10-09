import RecordRemover from 'akeneoreferenceentity/domain/remover/record';
import RecordCode from 'akeneoreferenceentity/domain/model/record/code';
import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import {deleteJSON} from 'akeneoreferenceentity/tools/fetch';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import errorHandler from 'akeneoreferenceentity/infrastructure/tools/error-handler';

const routing = require('routing');

export class RecordRemoverImplementation implements RecordRemover<ReferenceEntityIdentifier, RecordCode> {
  constructor() {
    Object.freeze(this);
  }

  async remove(
    referenceEntityIdentifier: ReferenceEntityIdentifier,
    recordCode: RecordCode
  ): Promise<ValidationError[] | null> {
    return await deleteJSON(
      routing.generate('akeneo_reference_entities_record_delete_rest', {
        recordCode: recordCode.stringValue(),
        referenceEntityIdentifier: referenceEntityIdentifier.stringValue(),
      })
    ).catch(errorHandler);
  }

  async removeAll(referenceEntityIdentifier: ReferenceEntityIdentifier): Promise<ValidationError[] | null> {
    return await deleteJSON(
      routing.generate('akeneo_reference_entities_record_delete_all_rest', {
        referenceEntityIdentifier: referenceEntityIdentifier.stringValue(),
      })
    ).catch(errorHandler);
  }
}

export default new RecordRemoverImplementation();
