import * as React from 'react';
import styled from 'styled-components';
import {ValidationError, formatParameters} from '@akeneo-pim-community/shared';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

const Container = styled.div`
  .AknFieldContainer-validationError {
    background-size: 20px;
  }
`;

type InputErrorsProps = {
  errors?: ValidationError[];
};

const InputErrors = ({errors = []}: InputErrorsProps) => {
  const translate = useTranslate();

  if (0 === errors.length) return null;

  return (
    <Container className="AknFieldContainer-footer AknFieldContainer-validationErrors">
      {formatParameters(errors).map((error, key) => (
        <span className="AknFieldContainer-validationError error-message" key={key}>
          {translate(error.messageTemplate, error.parameters, error.plural)}
        </span>
      ))}
    </Container>
  );
};

export {InputErrors};
