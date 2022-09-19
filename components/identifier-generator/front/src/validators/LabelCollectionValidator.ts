import {Validator} from './Validator';
import {LabelCollection} from '../models';
import {ValidationError} from './ValidationError';

const validateLabelCollection: Validator<LabelCollection> = (labelCollection, path) => {
  const result: ValidationError[] = [];

  Object.keys(labelCollection).forEach(locale => {
    if (locale.trim() === '') {
      result.push({path, message: 'Locale should not be empty'});
    }
  });

  Object.keys(labelCollection).forEach(locale => {
    if (labelCollection[locale].trim() === '') {
      result.push({path, message: `Label for ${locale} should not be empty`});
    }
  });

  // TODO: We hsould have a max length for labels !

  return result;
};

export {validateLabelCollection};
