import React from 'react';
import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import __ from 'akeneoassetmanager/tools/translator';
import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';
import {channelReferenceAreEqual} from 'akeneoassetmanager/domain/model/channel-reference';
import {localeReferenceAreEqual} from 'akeneoassetmanager/domain/model/locale-reference';

// TODO RAC-546: will be removed by mass edit
export const getErrorsView = (errors: ValidationError[], value: EditionValue) => {
  const errorMessages = errors
    .filter(
      (error: ValidationError) =>
        `values.${value.attribute.code}` === error.propertyPath &&
        channelReferenceAreEqual(error.invalidValue.channel, value.channel) &&
        localeReferenceAreEqual(error.invalidValue.locale, value.locale)
    )
    .map((error: ValidationError, key: number) => (
      <span className="AknFieldContainer-validationError" key={key}>
        {__(error.messageTemplate, error.parameters)}
      </span>
    ));

  if (0 === errorMessages.length) return null;

  return <div className="AknFieldContainer-footer AknFieldContainer-validationErrors">{errorMessages}</div>;
};
