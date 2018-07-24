import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';

export const attributeCreationStart = () => {
  return {type: 'ATTRIBUTE_CREATION_START'};
};

export const attributeCreationCodeUpdated = (value: string) => {
  return {type: 'ATTRIBUTE_CREATION_RECORD_CODE_UPDATED', value};
};

export const attributeCreationLabelUpdated = (value: string, locale: string) => {
  return {type: 'ATTRIBUTE_CREATION_LABEL_UPDATED', value, locale};
};

export const attributeCreationTypeUpdated = (attributeType: string) => {
  return {type: 'ATTRIBUTE_CREATION_TYPE_UPDATED', attributeType};
};

export const attributeCreationValuePerLocaleUpdated = (valuePerLocale: boolean) => {
  return {type: 'ATTRIBUTE_CREATION_VALUE_PER_LOCALE_UPDATED', valuePerLocale};
};

export const attributeCreationValuePerChannelUpdated = (valuePerChannel: boolean) => {
  return {type: 'ATTRIBUTE_CREATION_VALUE_PER_CHANNEL_UPDATED', valuePerChannel};
};

export const attributeCreationCancel = () => {
  return {type: 'ATTRIBUTE_CREATION_CANCEL'};
};

export const attributeCreationSubmission = () => {
  return {type: 'ATTRIBUTE_CREATION_SUBMISSION'};
};

export const attributeCreationSucceeded = () => {
  return {type: 'ATTRIBUTE_CREATION_SUCCEEDED'};
};

export const attributeCreationErrorOccured = (errors: ValidationError[]) => {
  return {type: 'ATTRIBUTE_CREATION_ERROR_OCCURED', errors};
};
