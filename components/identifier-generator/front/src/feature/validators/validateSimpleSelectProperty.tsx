import {Validator} from './Validator';
import {AbbreviationType, SimpleSelectProperty} from '../models';
import {Violation} from './Violation';

const validateSimpleSelectProperty: Validator<SimpleSelectProperty> = (simpleSelectProperty, path) => {
  const violations: Violation[] = [];

  if (!simpleSelectProperty.process.type) {
    violations.push({
      path,
      message: 'The empty values must be filled',
    });
  }

  if (
    simpleSelectProperty.process.type === AbbreviationType.TRUNCATE &&
    simpleSelectProperty.process.operator === null
  ) {
    violations.push({
      path,
      message: 'The empty values must be filled',
    });
  }

  return violations;
};

export {validateSimpleSelectProperty};
