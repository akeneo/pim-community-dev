import * as React from 'react';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';
import __ from 'akeneoenrichedentity/tools/translator';
import Value from 'akeneoenrichedentity/domain/model/record/value';
import {denormalizeChannelReference} from 'akeneoenrichedentity/domain/model/channel-reference';
import {denormalizeLocaleReference} from 'akeneoenrichedentity/domain/model/locale-reference';

export const getErrorsView = (errors: ValidationError[], value: Value) => {
  const errorMessages = errors
    .filter(
      (error: ValidationError) =>
        `values.${value.attribute.getCode().stringValue()}` === error.propertyPath &&
        denormalizeChannelReference(error.invalidValue.channel).equals(value.channel) &&
        denormalizeLocaleReference(error.invalidValue.locale).equals(value.locale)
    )
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
