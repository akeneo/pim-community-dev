import {NormalizedReferenceEntity} from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import File from 'akeneoreferenceentity/domain/model/file';

export const referenceEntityEditionReceived = (referenceEntity: NormalizedReferenceEntity) => {
  return {type: 'REFERENCE_ENTITY_EDITION_RECEIVED', referenceEntity};
};

export const referenceEntityRecordCountUpdated = (recordCount: number) => {
  return {type: 'GRID_TOTAL_COUNT_UPDATED', totalCount: recordCount};
};

export const referenceEntityEditionUpdated = (referenceEntity: NormalizedReferenceEntity) => {
  return {type: 'REFERENCE_ENTITY_EDITION_UPDATED', referenceEntity};
};

export const referenceEntityEditionLabelUpdated = (value: string, locale: string) => {
  return {type: 'REFERENCE_ENTITY_EDITION_LABEL_UPDATED', value, locale};
};

export const referenceEntityEditionImageUpdated = (image: File) => {
  return {type: 'REFERENCE_ENTITY_EDITION_IMAGE_UPDATED', image: image.normalize()};
};

export const referenceEntityEditionSubmission = () => {
  return {type: 'REFERENCE_ENTITY_EDITION_SUBMISSION'};
};

export const referenceEntityEditionSucceeded = () => {
  return {type: 'REFERENCE_ENTITY_EDITION_SUCCEEDED'};
};

export const referenceEntityEditionErrorOccured = (errors: ValidationError[]) => {
  return {type: 'REFERENCE_ENTITY_EDITION_ERROR_OCCURED', errors};
};
