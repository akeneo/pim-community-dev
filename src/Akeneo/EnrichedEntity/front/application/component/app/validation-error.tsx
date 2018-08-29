import * as React from 'react';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';
import __ from 'akeneoenrichedentity/tools/translator';

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
      <span className="AknFieldContainer-validationError">
        <i className="icon-warning-sign" />
        {errorMessages}
      </span>
    </div>
  );
};
