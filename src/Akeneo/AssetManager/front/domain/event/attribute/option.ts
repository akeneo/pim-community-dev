import {Option} from 'akeneoassetmanager/domain/model/attribute/type/option/option';
import {ValidationError} from '@akeneo-pim-community/shared';

export const optionEditionStart = (options: Option[]) => {
  return {type: 'OPTIONS_EDITION_START', options: options.map((option: Option) => option)};
};

export const optionEditionCancel = () => {
  return {type: 'OPTIONS_EDITION_CANCEL'};
};

export const optionEditionSelected = (id: any) => {
  return {type: 'OPTIONS_EDITION_SELECTED', id};
};

export const optionEditionLabelUpdated = (label: string, locale: string, id: any) => {
  return {type: 'OPTIONS_EDITION_LABEL_UPDATED', label, locale, id};
};

export const optionEditionCodeUpdated = (code: string, id: any) => {
  return {type: 'OPTIONS_EDITION_CODE_UPDATED', id, code};
};

export const optionEditionSubmission = () => {
  return {type: 'OPTIONS_EDITION_SUBMISSION'};
};

export const optionEditionSucceeded = () => {
  return {type: 'OPTIONS_EDITION_SUCCEEDED'};
};

export const optionEditionErrorOccured = (errors: ValidationError[]) => {
  return {type: 'OPTIONS_EDITION_ERROR_OCCURED', errors};
};

export const optionEditionDelete = (id: any) => {
  return {type: 'OPTIONS_EDITION_DELETE', id};
};
