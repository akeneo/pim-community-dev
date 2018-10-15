import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';

export const referenceEntityCreationStart = () => {
  return {type: 'REFERENCE_ENTITY_CREATION_START'};
};

export const referenceEntityCreationCodeUpdated = (value: string) => {
  return {type: 'REFERENCE_ENTITY_CREATION_CODE_UPDATED', value};
};

export const referenceEntityCreationLabelUpdated = (value: string, locale: string) => {
  return {type: 'REFERENCE_ENTITY_CREATION_LABEL_UPDATED', value, locale};
};

export const referenceEntityCreationCancel = () => {
  return {type: 'REFERENCE_ENTITY_CREATION_CANCEL'};
};

export const referenceEntityCreationSubmission = () => {
  return {type: 'REFERENCE_ENTITY_CREATION_SUBMISSION'};
};

export const referenceEntityCreationSucceeded = () => {
  return {type: 'REFERENCE_ENTITY_CREATION_SUCCEEDED'};
};

export const referenceEntityCreationErrorOccured = (errors: ValidationError[]) => {
  return {type: 'REFERENCE_ENTITY_CREATION_ERROR_OCCURED', errors};
};
