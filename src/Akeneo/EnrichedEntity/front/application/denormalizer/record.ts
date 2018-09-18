import Record, {NormalizedRecord, createRecord} from 'akeneoenrichedentity/domain/model/record/record';
import {createIdentifier} from 'akeneoenrichedentity/domain/model/record/identifier';
import {createIdentifier as createEnrichedEntityIdentifier} from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import {createCode} from 'akeneoenrichedentity/domain/model/record/code';
import {createLabelCollection} from 'akeneoenrichedentity/domain/model/label-collection';
import {createValueCollection} from 'akeneoenrichedentity/domain/model/record/value-collection';
import denormalizeValue from 'akeneoenrichedentity/application/denormalizer/record/value';
import {denormalizeFile} from 'akeneoenrichedentity/domain/model/file';

const denormalizeRecord = (normalizedRecord: NormalizedRecord): Record => {
  const identifier = createIdentifier(normalizedRecord.identifier);
  const code = createCode(normalizedRecord.code);
  const enrichedEntityIdentifier = createEnrichedEntityIdentifier(normalizedRecord.enriched_entity_identifier);
  const labelCollection = createLabelCollection(normalizedRecord.labels);
  const image = denormalizeFile(normalizedRecord.image);
  const valueCollection = createValueCollection(normalizedRecord.values.map(denormalizeValue));

  return createRecord(identifier, enrichedEntityIdentifier, code, labelCollection, image, valueCollection);
};

export default denormalizeRecord;
