import {NormalizedNumberAttribute} from 'akeneoassetmanager/domain/model/attribute/type/number';
import {NormalizedNumberAdditionalProperty} from 'akeneoassetmanager/domain/model/attribute/type/number';
import {NormalizedDecimalsAllowed} from 'akeneoassetmanager/domain/model/attribute/type/number/decimals-allowed';
import {NormalizedMinValue} from 'akeneoassetmanager/domain/model/attribute/type/number/min-value';
import {NormalizedMaxValue} from 'akeneoassetmanager/domain/model/attribute/type/number/max-value';

const numberAttributeReducer = (
  normalizedAttribute: NormalizedNumberAttribute,
  propertyCode: string,
  propertyValue: NormalizedNumberAdditionalProperty
): NormalizedNumberAttribute => {
  switch (propertyCode) {
    case 'decimals_allowed':
      const decimals_allowed = propertyValue as NormalizedDecimalsAllowed;
      return {...normalizedAttribute, decimals_allowed};
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
