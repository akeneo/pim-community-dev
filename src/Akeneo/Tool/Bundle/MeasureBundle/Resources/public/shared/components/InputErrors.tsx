import React from 'react';
import {ValidationError} from 'akeneomeasure/model/validation-error';

type InputErrorsProps = {
  errors: ValidationError[];
}

export const InputErrors = ({errors}: InputErrorsProps) => {
  return (
    <div className="AknFieldContainer-footer AknFieldContainer-validationErrors">
      {errors.map((error: ValidationError, key: number) => {
        return (
          <span className="AknFieldContainer-validationError error-message" key={key}>
            {error.message}
          </span>
        );
      })}
    </div>
  );
};
