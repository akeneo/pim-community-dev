import {createContext, useContext} from 'react';
import {filterErrors, ValidationError} from '@akeneo-pim-community/shared';

type ValidationErrorsValue = ValidationError[];

const ValidationErrorsContext = createContext<ValidationErrorsValue>([]);

const useValidationErrors = (propertyPath: string) => {
  const validationErrors = useContext(ValidationErrorsContext);

  return filterErrors(validationErrors, propertyPath);
};

export {ValidationErrorsContext, useValidationErrors};
