import EnrichedEntity, {createRecord} from 'akeneoenrichedentity/domain/model/record/record';
import Identifier, {createIdentifier} from 'akeneoenrichedentity/domain/model/record/identifier';
import EnrichedEntityIdentifier, {
  createIdentifier as createEnrichedEntityIdentifier,
} from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import LabelCollection, {createLabelCollection} from 'akeneoenrichedentity/domain/model/label-collection';
import {validateKeys} from 'akeneoenrichedentity/application/hydrator/hydrator';
import RecordCode, {createCode} from 'akeneoenrichedentity/domain/model/record/code';
import Image from 'akeneoenrichedentity/domain/model/image';

export const hydrator = (
  createRecord: (
    identifier: Identifier,
    enrichedEntityIdentifier: EnrichedEntityIdentifier,
    code: RecordCode,
    labelCollection: LabelCollection,
    image: Image | null
  ) => EnrichedEntity,
  createIdentifier: (identifier: string) => Identifier,
  createEnrichedEntityIdentifier: (identifier: string) => EnrichedEntityIdentifier,
  createRecordCode: (code: string) => RecordCode,
  createLabelCollection: (labelCollection: any) => LabelCollection
) => (backendRecord: any): EnrichedEntity => {
  const expectedKeys = ['identifier', 'enriched_entity_identifier', 'code', 'labels'];

  validateKeys(backendRecord, expectedKeys, 'The provided raw record seems to be malformed.');

  const identifier = createIdentifier(backendRecord.identifier);
  const enrichedEntityIdentifier = createEnrichedEntityIdentifier(backendRecord.enriched_entity_identifier);
  const code = createRecordCode(backendRecord.code);
  const labelCollection = createLabelCollection(backendRecord.labels);
  const image = backendRecord.image;

  return createRecord(identifier, enrichedEntityIdentifier, code, labelCollection, image);
};

export default hydrator(
  createRecord,
  createIdentifier,
  createEnrichedEntityIdentifier,
  createCode,
  createLabelCollection
);
