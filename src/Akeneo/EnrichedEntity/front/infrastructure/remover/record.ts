import RecordRemover from 'akeneoenrichedentity/domain/remover/record';
import RecordIdentifier from 'akeneoenrichedentity/domain/model/record/identifier';
import EnrichedEntityIdentifier from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import {deleteJSON} from 'akeneoenrichedentity/tools/fetch';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';

const routing = require('routing');

export class RecordRemoverImplementation implements RecordRemover<EnrichedEntityIdentifier, RecordIdentifier> {
  constructor() {
    Object.freeze(this);
  }

  async remove(
    enrichedEntityIdentifier: EnrichedEntityIdentifier,
    recordIdentifier: RecordIdentifier
  ): Promise<ValidationError[] | null> {
    return await deleteJSON(
      routing.generate('akeneo_enriched_entities_record_delete_rest', {
        recordIdentifier: recordIdentifier.normalize(),
        enrichedEntityIdentifier: enrichedEntityIdentifier.stringValue(),
      })
    ).catch(error => {
      //Todo change to error handler
      if (500 === error.status) {
        throw new Error('Internal Server error');
      }

      return error.responseJSON;
    });
  }
}

export default new RecordRemoverImplementation();
