import React from 'react';
import {ValidationError} from 'akeneomeasure/model/validation-error';

type InputErrorsProps = {
  errors: ValidationError[];
};

const InputErrors = ({errors}: InputErrorsProps) => (
  <div className="AknFieldContainer-footer AknFieldContainer-validationErrors">
    {errors.map((error: ValidationError, key: number) => (
      <span className="AknFieldContainer-validationError error-message" key={key}>
        {error.message}
      </span>
    ))}
  </div>
);

export {InputErrors};
