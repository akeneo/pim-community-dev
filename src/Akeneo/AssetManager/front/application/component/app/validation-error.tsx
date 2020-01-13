import * as React from 'react';
import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import __ from 'akeneoassetmanager/tools/translator';

const equalsFilter = (field: string) => (error: ValidationError) => field === error.propertyPath;

export const getErrorsView = (
  errors: ValidationError[],
  field: string,
  searchMethod: (field: string) => (error: ValidationError) => boolean = equalsFilter
) => {
  if (!Array.isArray(errors) || errors.find(searchMethod(field)) === undefined) {
    return null;
  }

  return (
    <div className="AknFieldContainer-footer AknFieldContainer-validationErrors">
      {errors.filter(searchMethod(field)).map((error: ValidationError, key: number) => {
        const path = error.propertyPath.substring(field.length);
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
