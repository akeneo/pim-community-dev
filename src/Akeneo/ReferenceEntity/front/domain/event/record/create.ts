import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';

export const recordCreationStart = () => {
  return {type: 'RECORD_CREATION_START'};
};

export const recordCreationRecordCodeUpdated = (value: string) => {
  return {type: 'RECORD_CREATION_RECORD_CODE_UPDATED', value};
};

export const recordCreationLabelUpdated = (value: string, locale: string) => {
  return {type: 'RECORD_CREATION_LABEL_UPDATED', value, locale};
};

export const recordCreationCancel = () => {
  return {type: 'RECORD_CREATION_CANCEL'};
};

export const recordCreationSubmission = () => {
  return {type: 'RECORD_CREATION_SUBMISSION'};
};

export const recordCreationSucceeded = () => {
  return {type: 'RECORD_CREATION_SUCCEEDED'};
};

export const recordCreationErrorOccurred = (errors: ValidationError[]) => {
  return {type: 'RECORD_CREATION_ERROR_OCCURRED', errors};
};
