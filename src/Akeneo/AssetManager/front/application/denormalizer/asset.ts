import Record, {NormalizedRecord, createRecord} from 'akeneoreferenceentity/domain/model/record/record';
import {createIdentifier} from 'akeneoreferenceentity/domain/model/record/identifier';
import {createIdentifier as createReferenceEntityIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import {createCode} from 'akeneoreferenceentity/domain/model/record/code';
import {createLabelCollection} from 'akeneoreferenceentity/domain/model/label-collection';
import {createValueCollection} from 'akeneoreferenceentity/domain/model/record/value-collection';
import denormalizeValue from 'akeneoreferenceentity/application/denormalizer/record/value';
import {denormalizeFile} from 'akeneoreferenceentity/domain/model/file';

const denormalizeRecord = (normalizedRecord: NormalizedRecord): Record => {
  const identifier = createIdentifier(normalizedRecord.identifier);
  const code = createCode(normalizedRecord.code);
  const referenceEntityIdentifier = createReferenceEntityIdentifier(normalizedRecord.reference_entity_identifier);
  const labelCollection = createLabelCollection(normalizedRecord.labels);
  const image = denormalizeFile(normalizedRecord.image);
  const valueCollection = createValueCollection(normalizedRecord.values.map(denormalizeValue));

  return createRecord(identifier, referenceEntityIdentifier, code, labelCollection, image, valueCollection);
};

export default denormalizeRecord;
