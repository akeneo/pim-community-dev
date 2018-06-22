import EnrichedEntity, {createRecord} from 'akeneoenrichedentity/domain/model/record/record';
import Identifier, {createIdentifier} from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import EnrichedEntityIdentifier, {
  createIdentifier as createEnrichedEntityIdentifier,
} from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import LabelCollection, {createLabelCollection} from 'akeneoenrichedentity/domain/model/label-collection';
import {validateKeys} from 'akeneoenrichedentity/application/hydrator/hydrator';

export const hydrator = (
  createRecord: (
    identifier: Identifier,
    enrichedEntityIdentifier: EnrichedEntityIdentifier,
    labelCollection: LabelCollection
  ) => EnrichedEntity,
  createIdentifier: (identifier: string) => Identifier,
  createEnrichedEntityIdentifier: (identifier: string) => EnrichedEntityIdentifier,
  createLabelCollection: (labelCollection: any) => LabelCollection
) => (backendRecord: any): EnrichedEntity => {
  const expectedKeys = ['identifier', 'enriched_entity_identifier', 'labels'];

  validateKeys(backendRecord, expectedKeys, 'The provided raw record seems to be malformed.');

  const identifier = createIdentifier(backendRecord.identifier);
  const enrichedEntityIdentifier = createEnrichedEntityIdentifier(backendRecord.enriched_entity_identifier);
  const labelCollection = createLabelCollection(backendRecord.labels);

  return createRecord(identifier, enrichedEntityIdentifier, labelCollection);
};

export default hydrator(createRecord, createIdentifier, createEnrichedEntityIdentifier, createLabelCollection);
