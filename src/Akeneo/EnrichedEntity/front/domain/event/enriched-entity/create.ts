import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';

export const enrichedEntityCreationStart = () => {
  return {type: 'ENRICHED_ENTITY_CREATION_START'};
};

export const enrichedEntityCreationCodeUpdated = (value: string) => {
  return {type: 'ENRICHED_ENTITY_CREATION_CODE_UPDATED', value};
};

export const enrichedEntityCreationLabelUpdated = (value: string, locale: string) => {
  return {type: 'ENRICHED_ENTITY_CREATION_LABEL_UPDATED', value, locale};
};

export const enrichedEntityCreationCancel = () => {
  return {type: 'ENRICHED_ENTITY_CREATION_CANCEL'};
};

export const enrichedEntityCreationSubmission = () => {
  return {type: 'ENRICHED_ENTITY_CREATION_SUBMISSION'};
};

export const enrichedEntityCreationSucceeded = () => {
  return {type: 'ENRICHED_ENTITY_CREATION_SUCCEEDED'};
};

export const enrichedEntityCreationErrorOccured = (errors: ValidationError[]) => {
  return {type: 'ENRICHED_ENTITY_CREATION_ERROR_OCCURED', errors};
};
