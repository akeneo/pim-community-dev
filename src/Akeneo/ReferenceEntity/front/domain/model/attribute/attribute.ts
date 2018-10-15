import {AttributeType} from 'akeneoreferenceentity/domain/model/attribute/minimal';
export {AttributeType} from 'akeneoreferenceentity/domain/model/attribute/minimal';
import {
  NormalizedTextAttribute,
  TextAttribute,
  ConcreteTextAttribute,
  TextAdditionalProperty,
  NormalizedTextAdditionalProperty,
} from 'akeneoreferenceentity/domain/model/attribute/type/text';
import {
  NormalizedImageAttribute,
  ImageAttribute,
  ConcreteImageAttribute,
  ImageAdditionalProperty,
  NormalizedImageAdditionalProperty,
} from 'akeneoreferenceentity/domain/model/attribute/type/image';

export interface NormalizableAdditionalProperty {
  normalize(): NormalizedAdditionalProperty;
}

export type NormalizedAdditionalProperty = NormalizedImageAdditionalProperty | NormalizedTextAdditionalProperty;
export type AdditionalProperty = ImageAdditionalProperty | TextAdditionalProperty;
export type NormalizedAttribute = NormalizedTextAttribute | NormalizedImageAttribute;
type Attribute = TextAttribute | ImageAttribute;

export default Attribute;

class InvalidAttributeTypeError extends Error {}

export const denormalizeAttribute = (normalizedAttribute: NormalizedAttribute) => {
  switch (normalizedAttribute.type) {
    case AttributeType.Text:
      return ConcreteTextAttribute.createFromNormalized(normalizedAttribute as NormalizedTextAttribute);
    case AttributeType.Image:
      return ConcreteImageAttribute.createFromNormalized(normalizedAttribute as NormalizedImageAttribute);
    default:
      throw new InvalidAttributeTypeError(`Attribute type "${normalizedAttribute.type}" is not supported`);
  }
};
