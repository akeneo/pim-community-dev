import Saver from 'akeneoenrichedentity/domain/saver/saver';
import Record from 'akeneoenrichedentity/domain/model/record/record';
import {postJSON} from 'akeneoenrichedentity/tools/fetch';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';
import handleError from 'akeneoenrichedentity/infrastructure/saver/error-handler';

const routing = require('routing');

export interface RecordSaver extends Saver<Record> {}

export class RecordSaverImplementation implements RecordSaver {
  constructor() {
    Object.freeze(this);
  }

  async save(record: Record): Promise<ValidationError[] | null> {
    const normalizedRecord = record.normalize() as any;
    normalizedRecord.enriched_entity_identifier = normalizedRecord.enrichedEntityIdentifier;

    return await postJSON(
      routing.generate('akeneo_enriched_entities_record_edit_rest', {
        enrichedEntityIdentifier: record.getEnrichedEntityIdentifier().stringValue(),
        identifier: record.getCode(),
      }),
      normalizedRecord
    ).catch(handleError);
  }

  async create(record: Record): Promise<ValidationError[] | null> {
    const normalizedRecord = record.normalize() as any;
    normalizedRecord.enriched_entity_identifier = normalizedRecord.enrichedEntityIdentifier;

    return await postJSON(
      routing.generate('akeneo_enriched_entities_record_create_rest', {
        enrichedEntityIdentifier: record.getEnrichedEntityIdentifier().stringValue(),
      }),
      normalizedRecord
    ).catch(handleError);
  }
}

export default new RecordSaverImplementation();
