import React from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy';
import {Helper} from 'akeneo-design-system';
import {formatParameters, ValidationError} from '../models/validation-error';

type InputErrorsProps = {
  errors?: ValidationError[];
};

const InputErrors = ({errors = []}: InputErrorsProps) => {
  const translate = useTranslate();

  if (0 === errors.length) return null;

  return (
    <>
      {formatParameters(errors).map((error, key) => (
        <Helper inline={true} level="error" key={key}>
          {translate(error.messageTemplate, error.parameters, error.plural)}
        </Helper>
      ))}
    </>
  );
};

export {InputErrors};
