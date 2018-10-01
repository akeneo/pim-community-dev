import Saver from 'akeneoreferenceentity/domain/saver/saver';
import Record from 'akeneoreferenceentity/domain/model/record/record';
import {postJSON} from 'akeneoreferenceentity/tools/fetch';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import handleError from 'akeneoreferenceentity/infrastructure/tools/error-handler';

const routing = require('routing');

export interface RecordSaver extends Saver<Record> {}

export class RecordSaverImplementation implements RecordSaver {
  constructor() {
    Object.freeze(this);
  }

  async save(record: Record): Promise<ValidationError[] | null> {
    const normalizedRecord = record.normalizeMinimal();

    return await postJSON(
      routing.generate('akeneo_reference_entities_record_edit_rest', {
        referenceEntityIdentifier: record.getReferenceEntityIdentifier().stringValue(),
        recordCode: record.getCode().stringValue(),
      }),
      normalizedRecord
    ).catch(handleError);
  }

  async create(record: Record): Promise<ValidationError[] | null> {
    const normalizedRecord = record.normalize() as any;

    return await postJSON(
      routing.generate('akeneo_reference_entities_record_create_rest', {
        referenceEntityIdentifier: record.getReferenceEntityIdentifier().stringValue(),
      }),
      normalizedRecord
    ).catch(handleError);
  }
}

export default new RecordSaverImplementation();
