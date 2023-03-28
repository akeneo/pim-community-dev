import {Validator} from './Validator';
import {ALLOWED_PROPERTY_NAMES, PROPERTY_NAMES, Structure} from '../models';
import {Violation} from './Violation';
import {validateFreeText} from './validateFreeText';
import {validateAutoNumber} from './validateAutoNumber';
import {validateFamilyProperty} from './validateFamilyProperty';
import {validateAttributeProperty} from './validateAttributeProperty';

const validateStructure: Validator<Structure | undefined> = (structure, path) => {
  const violations: Violation[] = [];

  if (structure?.length === 0) {
    violations.push({
      path,
      message: 'The structure must contain at least one property',
    });
  }

  structure?.forEach((property, i) => {
    const subPath = `${path}[${i}]`;
    if (!ALLOWED_PROPERTY_NAMES.includes(property.type)) {
      violations.push({
        path: subPath,
        message: `The property type "${
          property.type
        }" is unknown. Please choose one of the following: ${ALLOWED_PROPERTY_NAMES.join(', ')}`,
      });
    }

    if (property.type === PROPERTY_NAMES.FREE_TEXT) {
      violations.push(...validateFreeText(property, subPath));
    }
    if (property.type === PROPERTY_NAMES.AUTO_NUMBER) {
      violations.push(...validateAutoNumber(property, subPath));
    }
    if (property.type === PROPERTY_NAMES.FAMILY) {
      violations.push(...validateFamilyProperty(property, subPath));
    }
    if (property.type === PROPERTY_NAMES.SIMPLE_SELECT || property.type === PROPERTY_NAMES.REF_ENTITY) {
      violations.push(...validateAttributeProperty(property, subPath));
    }
  });

  return violations;
};

export {validateStructure};
