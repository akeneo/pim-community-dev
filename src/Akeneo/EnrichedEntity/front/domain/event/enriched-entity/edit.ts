import {NormalizedEnrichedEntity} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';
import Image from 'akeneoenrichedentity/domain/model/image';

export const enrichedEntityEditionReceived = (enrichedEntity: NormalizedEnrichedEntity) => {
  return {type: 'ENRICHED_ENTITY_EDITION_RECEIVED', enrichedEntity};
};

export const enrichedEntityEditionUpdated = (enrichedEntity: NormalizedEnrichedEntity) => {
  return {type: 'ENRICHED_ENTITY_EDITION_UPDATED', enrichedEntity};
};

export const enrichedEntityEditionLabelUpdated = (value: string, locale: string) => {
  return {type: 'ENRICHED_ENTITY_EDITION_LABEL_UPDATED', value, locale};
};

export const enrichedEntityEditionImageUpdated = (image: Image | null) => {
  return {type: 'ENRICHED_ENTITY_EDITION_IMAGE_UPDATED', image};
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
