import {Validator} from './Validator';
import {ALLOWED_PROPERTY_NAMES, Structure} from '../models';
import {Violation} from './Violation';

const validateStructure: Validator<Structure | undefined> = (structure, path) => {
  const violations: Violation[] = [];

  if (structure?.length === 0) {
    violations.push({
      path,
      message: 'The structure must contain at least 1 property',
    });
  }

  structure?.forEach((property, i) => {
    if (!ALLOWED_PROPERTY_NAMES.includes(property.type)) {
      violations.push({
        path: `${path}[${i}]`,
        message: `The property type "${
          property.type
        }" is unknown. Please choose one of the following: ${ALLOWED_PROPERTY_NAMES.join(', ')}`,
      });
    }
  });

  return violations;
};

export {validateStructure};
