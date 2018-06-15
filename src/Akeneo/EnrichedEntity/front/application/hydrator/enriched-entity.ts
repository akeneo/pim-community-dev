import EnrichedEntity, {createEnrichedEntity} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import Identifier, {createIdentifier} from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import LabelCollection, {createLabelCollection} from 'akeneoenrichedentity/domain/model/label-collection';

class InvalidRawObject extends Error {
  constructor(message: string, expectedKeys: string[], invalidKeys: string[], malformedObject: any) {
    super(`${message}
Expected keys are ${expectedKeys.join(', ')}
Received object:
${JSON.stringify(malformedObject)}
Invalid keys: ${invalidKeys.join(', ')}`);
  }
}

const validateKeys = (object: any, keys: string[]) => {
  const invalidKeys = keys.filter((key: string) => undefined === object[key]);

  if (0 !== invalidKeys.length) {
    throw new InvalidRawObject('The provided raw enriched entity seems to be malformed.', keys, invalidKeys, object);
  }
};

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
