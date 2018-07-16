export interface Error {
    propertyPath: string;
    message: string;
}

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

export const enrichedEntityCreationErrorOccured = (errors: Error[]) => {
    return {type: 'ENRICHED_ENTITY_CREATION_ERROR_OCCURED', errors};
};
