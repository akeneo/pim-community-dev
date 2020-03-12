import React from 'react';
import {ValidationError} from 'akeneomeasure/model/validation-error';

const samePropertyPath = (propertyPath: string) => (error: ValidationError) => propertyPath === error.property;

type InputErrorsProps = {
  errors: ValidationError[];
  propertyPath: string;
  searchMethod?: (propertyPath: string) => (error: ValidationError) => boolean;
}

export const InputErrors = ({errors, propertyPath, searchMethod = samePropertyPath}: InputErrorsProps) => {
  return (
    <div className="AknFieldContainer-footer AknFieldContainer-validationErrors">
      {errors.filter(searchMethod(propertyPath)).map((error: ValidationError, key: number) => {
        const path = error.property.substring(propertyPath.length);
        return (
          <span className="AknFieldContainer-validationError error-message" key={key}>
            {path !== '' ? path + ': ' : null}
            {error.message}
          </span>
        );
      })}
    </div>
  );
};
