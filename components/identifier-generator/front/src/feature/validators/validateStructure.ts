import {Validator} from './Validator';
import {ALLOWED_PROPERTY_NAMES, PROPERTY_NAMES, Structure} from '../models';
import {Violation} from './Violation';
import {validateFreeText} from './validateFreeText';

const validateStructure: Validator<Structure | undefined> = (structure, path) => {
  const violations: Violation[] = [];

  if (structure?.length === 0) {
    violations.push({
      path,
      message: 'The structure must contain at least 1 property',
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
  });

  return violations;
};

export {validateStructure};
