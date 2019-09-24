import {isString} from 'akeneoassetmanager/domain/model/utils';
import {AttributeCode} from 'akeneopimenrichmentassetmanager/platform/model/structure/attribute';
import {Context} from 'akeneopimenrichmentassetmanager/platform/model/context';
import {LocaleReference} from 'akeneoassetmanager/domain/model/locale';
import {ChannelReference} from 'akeneoassetmanager/domain/model/channel';

export type ValidationError = {
  attribute: AttributeCode;
  locale: LocaleReference;
  message: string;
  channel: ChannelReference;
};

export const getValidationErrorsForAttribute = (
  attributeCode: AttributeCode,
  context: Context,
  errors: ValidationError[]
): ValidationError[] => {
  const errorsForAttribute = errors.filter(
    (error: ValidationError) =>
      attributeCode === error.attribute && context.channel === error.channel && context.locale === error.locale
  );

  return errorsForAttribute.map((error: ValidationError) => error);
};

export const isValidErrorCollection = (errors: any): errors is ValidationError[] => {
  if (!Array.isArray(errors)) {
    return false;
  }

  return errors.some((error: any) => isValidError(error));
};

const isValidError = (error: any): error is ValidationError => {
  if (!isString(error.attribute)) {
    return false;
  }

  if (undefined === error.locale || (null !== error.locale && 'string' !== typeof error.locale)) {
    return false;
  }

  if (!isString(error.message)) {
    return false;
  }

  if (undefined === error.scope || (null !== error.scope && 'string' !== typeof error.scope)) {
    return false;
  }

  return true;
};

export const denormalizeErrorCollection = (normalizedErrors: any): ValidationError[] => {
  const errorCollection = normalizedErrors.map(
    (normalizedError: any): ValidationError => {
      const error = {
        ...normalizedError,
        channel: normalizedError.scope,
      };

      return error;
    }
  );

  return errorCollection;
};
