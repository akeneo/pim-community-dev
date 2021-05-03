import {createContext, useContext} from 'react';
import {filterErrors, formatParameters, getErrorsForPath, ValidationError} from '@akeneo-pim-community/shared';

type ValidationErrorsValue = ValidationError[];

const ValidationErrorsContext = createContext<ValidationErrorsValue>([]);

const useValidationErrors = (propertyPath: string, exactMatch: boolean) => {
  const validationErrors = useContext(ValidationErrorsContext);

  const errors = exactMatch ? getErrorsForPath(validationErrors, propertyPath) : filterErrors(validationErrors, propertyPath);

  return formatParameters(errors);
};

export {ValidationErrorsContext, useValidationErrors};
