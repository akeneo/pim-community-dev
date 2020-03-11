import React, {useContext} from 'react';
import {ValidationError} from 'akeneomeasure/shared/model/ValidationError';
import {TranslateContext} from 'akeneomeasure/shared/translate/translate-context';

const samePropertyPath = (propertyPath: string) => (error: ValidationError) => propertyPath === error.propertyPath;

type InputErrorsProps = {
  errors: ValidationError[];
  propertyPath: string;
  searchMethod?: (propertyPath: string) => (error: ValidationError) => boolean;
}

export const InputErrors = ({errors, propertyPath, searchMethod = samePropertyPath}: InputErrorsProps) => {
  const __ = useContext(TranslateContext);

  return (
    <div className="AknFieldContainer-footer AknFieldContainer-validationErrors">
      {errors.filter(searchMethod(propertyPath)).map((error: ValidationError, key: number) => {
        const path = error.propertyPath.substring(propertyPath.length);
        const errorMessage = __(error.messageTemplate, error.parameters);
        return (
          <span className="AknFieldContainer-validationError error-message" key={key}>
            {path !== '' ? path + ': ' : null}
            {errorMessage}
          </span>
        );
      })}
    </div>
  );
};
