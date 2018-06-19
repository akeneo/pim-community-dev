import EnrichedEntity, {createEnrichedEntity} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import Identifier, {createIdentifier} from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import LabelCollection, {createLabelCollection} from 'akeneoenrichedentity/domain/model/label-collection';
import {validateKeys} from 'akeneoenrichedentity/application/hydrator/hydrator';

export const hydrator = (
  createEnrichedEntity: (identifier: Identifier, labelCollection: LabelCollection) => EnrichedEntity,
  createIdentifier: (identifier: string) => Identifier,
  createLabelCollection: (labelCollection: any) => LabelCollection
) => (backendEnrichedEntity: any): EnrichedEntity => {
  const expectedKeys = ['identifier', 'labels'];

  validateKeys(backendEnrichedEntity, expectedKeys);

  const identifier = createIdentifier(backendEnrichedEntity.identifier);
  const labelCollection = createLabelCollection(backendEnrichedEntity.labels);

  return createEnrichedEntity(identifier, labelCollection);
};

export default hydrator(createEnrichedEntity, createIdentifier, createLabelCollection);
