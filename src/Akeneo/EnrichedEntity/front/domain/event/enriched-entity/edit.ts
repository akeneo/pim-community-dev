import {NormalizedEnrichedEntity} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';
import File from 'akeneoenrichedentity/domain/model/file';

export const enrichedEntityEditionReceived = (enrichedEntity: NormalizedEnrichedEntity) => {
  return {type: 'ENRICHED_ENTITY_EDITION_RECEIVED', enrichedEntity};
};

export const enrichedEntityEditionUpdated = (enrichedEntity: NormalizedEnrichedEntity) => {
  return {type: 'ENRICHED_ENTITY_EDITION_UPDATED', enrichedEntity};
};

export const enrichedEntityEditionLabelUpdated = (value: string, locale: string) => {
  return {type: 'ENRICHED_ENTITY_EDITION_LABEL_UPDATED', value, locale};
};

export const enrichedEntityEditionImageUpdated = (image: File) => {
  return {type: 'ENRICHED_ENTITY_EDITION_IMAGE_UPDATED', image: image.normalize()};
};

export const enrichedEntityEditionSubmission = () => {
  return {type: 'ENRICHED_ENTITY_EDITION_SUBMISSION'};
};

export const enrichedEntityEditionSucceeded = () => {
  return {type: 'ENRICHED_ENTITY_EDITION_SUCCEEDED'};
};

export const enrichedEntityEditionErrorOccured = (errors: ValidationError[]) => {
  return {type: 'ENRICHED_ENTITY_EDITION_ERROR_OCCURED', errors};
};
