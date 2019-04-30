import {NormalizedNumberAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/number';

const numberAttributeReducer = (
  normalizedAttribute: NormalizedNumberAttribute,
  propertyCode: string
): NormalizedNumberAttribute => {
  switch (propertyCode) {
    default:
      break;
  }

  return normalizedAttribute;
};

export const reducer = numberAttributeReducer;
