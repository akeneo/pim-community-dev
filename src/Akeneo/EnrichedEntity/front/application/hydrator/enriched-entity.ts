import EnrichedEntity, {
  denormalizeEnrichedEntity,
  NormalizedEnrichedEntity,
} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import {validateKeys} from 'akeneoenrichedentity/application/hydrator/hydrator';

export const hydrator = (
  denormalizeEnrichedEntity: (normalizedEnrichedEntity: NormalizedEnrichedEntity) => EnrichedEntity
) => (backendEnrichedEntity: any): EnrichedEntity => {
  const expectedKeys = ['identifier', 'labels', 'image'];

  validateKeys(backendEnrichedEntity, expectedKeys, 'The provided raw enriched entity seems to be malformed.');
  return denormalizeEnrichedEntity(backendEnrichedEntity);
};

export default hydrator(denormalizeEnrichedEntity);
