import Saver from 'akeneoenrichedentity/domain/saver/saver';
import Record from 'akeneoenrichedentity/domain/model/record/record';
import {postJSON} from 'akeneoenrichedentity/tools/fetch';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';

const routing = require('routing');

export interface RecordSaver extends Saver<Record> {}

export class RecordSaverImplementation implements RecordSaver {
  constructor() {
    Object.freeze(this);
  }

  async save(record: Record): Promise<ValidationError[] | null> {
    return await postJSON(
      routing.generate('akeneo_enriched_entities_record_edit_rest', {
        enrichedEntityIdentifier: record.getEnrichedEntityIdentifier().stringValue(),
        identifier: record.getIdentifier().stringValue(),
      }),
      record.normalize()
    ).catch(error => {
      if (500 === error.status) {
        throw new Error('Internal Server error');
      }

      return error.responseJSON;
    });
  }

  async create(record: Record): Promise<ValidationError[] | null> {
    return await postJSON(
      routing.generate('akeneo_enriched_entities_record_create_rest', {
        enrichedEntityIdentifier: record.getEnrichedEntityIdentifier().stringValue(),
      }),
      record.normalize()
    ).catch(error => {
      if (500 === error.status) {
        throw new Error('Internal Server error');
      }

      return error.responseJSON;
    });
  }
}

export default new RecordSaverImplementation();
