import {Validator} from './Validator';
import {AbbreviationType, FamilyCodeProperty} from '../models';
import {Violation} from './Violation';

const validateFamilyProperty: Validator<FamilyCodeProperty> = (familyCode, path) => {
  const violations: Violation[] = [];

  if (!familyCode.process.type) {
    violations.push({
      path,
      message: 'Family code abbreviation type must be filled',
    });
  }

  if (
    familyCode.process.type === AbbreviationType.TRUNCATE &&
    (!familyCode.process.operator || !familyCode.process.value)
  ) {
    violations.push({
      path: path,
      message: 'The values must be filled',
    });
  }

  return violations;
};

export {validateFamilyProperty};
