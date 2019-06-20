import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import {NormalizedCode} from 'akeneoreferenceentity/domain/model/code';
import {NormalizedLocaleCode} from 'akeneoreferenceentity/domain/model/locale';

export const referenceEntityCreationStart = () => {
  return {type: 'REFERENCE_ENTITY_CREATION_START'};
};

export const referenceEntityCreationCodeUpdated = (value: NormalizedCode) => {
  return {type: 'REFERENCE_ENTITY_CREATION_CODE_UPDATED', value};
};

export const referenceEntityCreationLabelUpdated = (value: string, locale: NormalizedLocaleCode) => {
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
