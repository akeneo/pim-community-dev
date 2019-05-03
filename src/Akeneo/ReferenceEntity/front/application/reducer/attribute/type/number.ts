import {NormalizedNumberAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/number';
import {NormalizedNumberAdditionalProperty} from 'akeneoreferenceentity/domain/model/attribute/type/number';
import {NormalizedIsDecimal} from 'akeneoreferenceentity/domain/model/attribute/type/number/is-decimal';

const numberAttributeReducer = (
  normalizedAttribute: NormalizedNumberAttribute,
  propertyCode: string,
  propertyValue: NormalizedNumberAdditionalProperty
): NormalizedNumberAttribute => {
  switch (propertyCode) {
    case 'is_decimal':
      const is_decimal = propertyValue as NormalizedIsDecimal;
      return {...normalizedAttribute, is_decimal};
    default:
      break;
  }

  return normalizedAttribute;
};

export const reducer = numberAttributeReducer;
