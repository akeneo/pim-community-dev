import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import File from 'akeneoreferenceentity/domain/model/file';
import Record from 'akeneoreferenceentity/domain/model/record/record';
import Value from 'akeneoreferenceentity/domain/model/record/value';

export const recordEditionReceived = (record: Record) => {
  return {type: 'RECORD_EDITION_RECEIVED', record: record.normalize()};
};

export const recordEditionUpdated = (record: Record) => {
  return {type: 'RECORD_EDITION_UPDATED', record};
};

export const recordEditionLabelUpdated = (label: string, locale: string) => {
  return {type: 'RECORD_EDITION_LABEL_UPDATED', label, locale};
};

export const recordEditionImageUpdated = (image: File) => {
  return {type: 'RECORD_EDITION_IMAGE_UPDATED', image: image.normalize()};
};

export const recordEditionValueUpdated = (value: Value) => {
  return {type: 'RECORD_EDITION_VALUE_UPDATED', value: value.normalize()};
};

export const recordEditionSubmission = () => {
  return {type: 'RECORD_EDITION_SUBMISSION'};
};

export const recordEditionSucceeded = () => {
  return {type: 'RECORD_EDITION_SUCCEEDED'};
};

export const recordEditionErrorOccured = (errors: ValidationError[]) => {
  return {type: 'RECORD_EDITION_ERROR_OCCURED', errors};
};
