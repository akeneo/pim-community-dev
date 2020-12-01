import React from 'react';
import {AttributeCode} from 'akeneoassetmanager/platform/model/structure/attribute';
import {ValidationError, getValidationErrorsForAttribute} from 'akeneoassetmanager/platform/model/validation-error';
import {Context} from 'akeneoassetmanager/domain/model/context';
import {Helper} from 'akeneo-design-system';

type ValidationErrorCollectionProps = {
  attributeCode: AttributeCode;
  context: Context;
  errors: ValidationError[];
};

const ValidationErrorCollection = ({attributeCode, context, errors}: ValidationErrorCollectionProps) => {
  const errorCollection = getValidationErrorsForAttribute(attributeCode, context, errors);

  if (0 === errorCollection.length) {
    return null;
  }

  return (
    <>
      {errorCollection.map(({message}: ValidationError, index: number) => (
        <Helper key={index} level="error">
          {message}
        </Helper>
      ))}
    </>
  );
};

export {ValidationErrorCollection};
