import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';

export const postSave = () => {
  return {type: 'POST_SAVE'};
};

export const failSave = (errors: ValidationError[]) => {
  return {type: 'FAIL_SAVE', errors};
};
