import React from 'react';
import {ValidationError, formatParameters} from '@akeneo-pim-community/shared';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {Helper} from 'akeneo-design-system';

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
