import {Validator} from './Validator';
import {LabelCollection} from '../models';
import {Violation} from './Violation';

const validateLabelCollection: Validator<LabelCollection> = (labelCollection, path) => {
  const violations: Violation[] = [];

  Object.keys(labelCollection).forEach(locale => {
    if (locale.trim() === '') {
      violations.push({path, message: 'Locale should not be empty'});
    }
  });

  Object.keys(labelCollection).forEach(locale => {
    if (labelCollection[locale].trim() === '') {
      violations.push({path, message: `Label for ${locale} should not be empty`});
    }
  });

  // TODO: We hsould have a max length for labels !

  return violations;
};

export {validateLabelCollection};
