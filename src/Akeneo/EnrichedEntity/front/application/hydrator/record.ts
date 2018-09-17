import EnrichedEntity, {denormalizeRecord} from 'akeneoenrichedentity/domain/model/record/record';
import {validateKeys} from 'akeneoenrichedentity/application/hydrator/hydrator';

export default (backendRecord: any): EnrichedEntity => {
  backendRecord.image = undefined === backendRecord.image ? null : backendRecord.image;

  const expectedKeys = ['identifier', 'enriched_entity_identifier', 'code', 'labels', 'image'];

  validateKeys(backendRecord, expectedKeys, 'The provided raw record seems to be malformed.');

  return denormalizeRecord(backendRecord);
};
