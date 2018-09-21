import RecordRemover from 'akeneoenrichedentity/domain/remover/record';
import RecordCode from 'akeneoenrichedentity/domain/model/record/code';
import EnrichedEntityIdentifier from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import {deleteJSON} from 'akeneoenrichedentity/tools/fetch';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';
import errorHandler from 'akeneoenrichedentity/infrastructure/tools/error-handler';

const routing = require('routing');

export class RecordRemoverImplementation implements RecordRemover<EnrichedEntityIdentifier, RecordCode> {
  constructor() {
    Object.freeze(this);
  }

  async remove(
    enrichedEntityIdentifier: EnrichedEntityIdentifier,
    recordCode: RecordCode
  ): Promise<ValidationError[] | null> {
    return await deleteJSON(
      routing.generate('akeneo_enriched_entities_record_delete_rest', {
        recordCode: recordCode.stringValue(),
        enrichedEntityIdentifier: enrichedEntityIdentifier.stringValue(),
      })
    ).catch(errorHandler);
  }
}

export default new RecordRemoverImplementation();
