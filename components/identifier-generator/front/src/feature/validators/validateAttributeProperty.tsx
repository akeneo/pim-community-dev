import {Validator} from './Validator';
import {AbbreviationType, RefEntityProperty, SimpleSelectProperty} from '../models';
import {Violation} from './Violation';

const validateAttributeProperty: Validator<SimpleSelectProperty | RefEntityProperty> = (attributeProperty, path) => {
  const violations: Violation[] = [];

  if (!attributeProperty.process.type) {
    violations.push({
      path,
      message: 'The empty values must be filled',
    });
  }

  if (
    attributeProperty.process.type === AbbreviationType.TRUNCATE &&
    (attributeProperty.process.operator === null ||
      attributeProperty.process.value === null ||
      attributeProperty.process.value === undefined)
  ) {
    violations.push({
      path,
      message: 'The empty values must be filled',
    });
  }

  return violations;
};

export {validateAttributeProperty};
