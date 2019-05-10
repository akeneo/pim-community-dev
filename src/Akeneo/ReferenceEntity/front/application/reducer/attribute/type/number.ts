import {NormalizedNumberAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/number';
import {NormalizedNumberAdditionalProperty} from 'akeneoreferenceentity/domain/model/attribute/type/number';
import {NormalizedIsDecimal} from 'akeneoreferenceentity/domain/model/attribute/type/number/is-decimal';
import {NormalizedMinValue} from 'akeneoreferenceentity/domain/model/attribute/type/number/min-value';
import {NormalizedMaxValue} from 'akeneoreferenceentity/domain/model/attribute/type/number/max-value';

const numberAttributeReducer = (
  normalizedAttribute: NormalizedNumberAttribute,
  propertyCode: string,
  propertyValue: NormalizedNumberAdditionalProperty
): NormalizedNumberAttribute => {
  switch (propertyCode) {
    case 'is_decimal':
      const is_decimal = propertyValue as NormalizedIsDecimal;
      return {...normalizedAttribute, is_decimal};
    case 'min_value':
      const min_value = propertyValue as NormalizedMinValue;
      return {...normalizedAttribute, min_value};
    case 'max_value':
      const max_value = propertyValue as NormalizedMaxValue;
      return {...normalizedAttribute, max_value};
    default:
      break;
  }

  return normalizedAttribute;
};

export const reducer = numberAttributeReducer;
