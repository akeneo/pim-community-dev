import {Validator} from './Validator';
import {LabelCollection} from '../models';
import {Violation} from './Violation';

const validateLabelCollection: Validator<LabelCollection> = (labelCollection, path) => {
  const violations: Violation[] = [];

  Object.keys(labelCollection).forEach(locale => {
    if (locale.trim() === '') {
      violations.push({path, message: 'The locale must be filled'});
    }
  });

  Object.keys(labelCollection).forEach(locale => {
    if (labelCollection[locale].length > 255) {
      violations.push({path, message: `The label for "${locale}" is too long: it must be 255 characters or less`});
    }
  });

  return violations;
};

export {validateLabelCollection};
