import ReferenceEntity, {
  denormalizeReferenceEntity,
  NormalizedReferenceEntity,
} from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import {validateKeys} from 'akeneoreferenceentity/application/hydrator/hydrator';

export const hydrator = (
  denormalizeReferenceEntity: (normalizedReferenceEntity: NormalizedReferenceEntity) => ReferenceEntity
) => (backendReferenceEntity: any): ReferenceEntity => {
  backendReferenceEntity.attribute_as_image =
    undefined === backendReferenceEntity.attribute_as_image ? null : backendReferenceEntity.attribute_as_image;
  backendReferenceEntity.attribute_as_label =
    undefined === backendReferenceEntity.attribute_as_label ? null : backendReferenceEntity.attribute_as_label;

  const expectedKeys = ['identifier', 'labels', 'image', 'attribute_as_image', 'attribute_as_label'];

  validateKeys(backendReferenceEntity, expectedKeys, 'The provided raw reference entity seems to be malformed.');
  return denormalizeReferenceEntity(backendReferenceEntity);
};

export default hydrator(denormalizeReferenceEntity);
