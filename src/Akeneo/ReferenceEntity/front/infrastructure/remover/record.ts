import RecordRemover from 'akeneoreferenceentity/domain/remover/record';
import RecordCode from 'akeneoreferenceentity/domain/model/record/code';
import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import {deleteJSON} from 'akeneoreferenceentity/tools/fetch';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import errorHandler from 'akeneoreferenceentity/infrastructure/tools/error-handler';
import {Query} from 'akeneoreferenceentity/domain/fetcher/fetcher';

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

  async removeFromQuery(referenceEntityIdentifier: ReferenceEntityIdentifier, query: Query): Promise<Response> {
    const url = routing.generate('akeneo_reference_entities_record_mass_delete_rest', {
      referenceEntityIdentifier: referenceEntityIdentifier.stringValue(),
    });

    return await fetch(url, {
      method: 'DELETE',
      body: JSON.stringify(query),
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });
  }
}

export default new RecordRemoverImplementation();
