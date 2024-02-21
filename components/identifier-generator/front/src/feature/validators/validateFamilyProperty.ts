import {Validator} from './Validator';
import {AbbreviationType, FamilyProperty} from '../models';
import {Violation} from './Violation';

const validateFamilyProperty: Validator<FamilyProperty> = (familyCode, path) => {
  const violations: Violation[] = [];

  if (!familyCode.process.type) {
    violations.push({
      path,
      message: 'The empty values must be filled',
    });
  }

  if (
    familyCode.process.type === AbbreviationType.TRUNCATE &&
    (!familyCode.process.operator || null === familyCode.process.value || undefined === familyCode.process.value)
  ) {
    violations.push({
      path: path,
      message: 'The empty values must be filled',
    });
  }

  if (
    familyCode.process.type === AbbreviationType.TRUNCATE &&
    familyCode.process?.value !== null &&
    familyCode.process?.value !== undefined &&
    (!/^\d+$/.exec(familyCode.process?.value.toString()) ||
      familyCode.process?.value < 1 ||
      familyCode.process?.value > 5)
  ) {
    violations.push({
      path: path,
      message: 'Please choose a number between 1 and 5',
    });
  }

  return violations;
};

export {validateFamilyProperty};
