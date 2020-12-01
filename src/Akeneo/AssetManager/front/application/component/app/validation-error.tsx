import React from 'react';
import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import {Helper} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

const equalsFilter = (field: string) => (error: ValidationError) => field === error.propertyPath;
const startsWith = (field: string) => (error: ValidationError) => error.propertyPath.indexOf(field) === 0;

const getErrorsView = (
  errors: ValidationError[],
  field: string,
  searchMethod: (field: string) => (error: ValidationError) => boolean = equalsFilter
) => {
  const translate = useTranslate();

  if (!Array.isArray(errors) || errors.find(searchMethod(field)) === undefined) {
    return null;
  }

  return (
    <div className="AknFieldContainer-footer AknFieldContainer-validationErrors">
      {errors.filter(searchMethod(field)).map((error: ValidationError, index) => {
        const path = error.propertyPath.substring(field.length);
        const errorMessage = translate(error.messageTemplate, error.parameters);

        return (
          <Helper key={index} inline={true} level="error">
            {'' !== path ? `${path}: ` : ''}
            {errorMessage}
          </Helper>
        );
      })}
    </div>
  );
};

const getErrorsViewStartedWith = (errors: ValidationError[], field: string) => getErrorsView(errors, field, startsWith);

export {getErrorsView, getErrorsViewStartedWith};
