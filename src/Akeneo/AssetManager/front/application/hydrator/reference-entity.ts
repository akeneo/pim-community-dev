import ReferenceEntity, {
  denormalizeReferenceEntity,
  NormalizedReferenceEntity,
} from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import {validateKeys} from 'akeneoreferenceentity/application/hydrator/hydrator';

export const hydrator = (
  denormalizeReferenceEntity: (normalizedReferenceEntity: NormalizedReferenceEntity) => ReferenceEntity
) => (backendReferenceEntity: any): ReferenceEntity => {
  const expectedKeys = ['identifier', 'labels', 'image', 'attribute_as_image', 'attribute_as_label'];

  validateKeys(backendReferenceEntity, expectedKeys, 'The provided raw reference entity seems to be malformed.');
  return denormalizeReferenceEntity(backendReferenceEntity);
};

export default hydrator(denormalizeReferenceEntity);
