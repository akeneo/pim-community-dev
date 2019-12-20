import * as React from 'react';
import ValidationError from 'akeneoassetmanager/domain/model/validation-error';
import __ from 'akeneoassetmanager/tools/translator';
import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';
import {channelReferenceAreEqual} from 'akeneoassetmanager/domain/model/channel-reference';
import {localeReferenceAreEqual} from 'akeneoassetmanager/domain/model/locale-reference';

export const getErrorsView = (errors: ValidationError[], value: EditionValue) => {
  const errorMessages = errors
    .filter(
      (error: ValidationError) =>
        `values.${value.attribute.code}` === error.propertyPath &&
        channelReferenceAreEqual(error.invalidValue.channel, value.channel) &&
        localeReferenceAreEqual(error.invalidValue.locale, value.locale)
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
