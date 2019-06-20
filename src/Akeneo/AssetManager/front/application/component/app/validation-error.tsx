import * as React from 'react';
import ValidationError from 'akeneoassetmanager/domain/model/validation-error';
import __ from 'akeneoassetmanager/tools/translator';

export const getErrorsView = (errors: ValidationError[], field: string) => {
  const errorMessages = errors
    .filter((error: ValidationError) => field === error.propertyPath)
    .map((error: ValidationError, key: number) => {
      return (
        <span className="error-message" key={key}>
          {__(error.messageTemplate, error.parameters)}
        </span>
      );
    });

  if (0 === errorMessages.length) {
    return null;
  }

  return (
    <div className="AknFieldContainer-footer AknFieldContainer-validationErrors">
      <span className="AknFieldContainer-validationError">{errorMessages}</span>
    </div>
  );
};
