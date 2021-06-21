import React from 'react';
import styled from 'styled-components';
import {filterErrors, LocaleCode, ValidationError} from '@akeneo-pim-community/shared';
import {OperatorSelector, Operator} from './OperatorSelector';
import {LocalesSelector} from './LocalesSelector';

const Container = styled.div`
  display: flex;
  flex-direction: column;
  gap: 15px;
  justify-content: space-between;
  width: 100%;
`;

type CompletenessFilterProps = {
  operator: Operator;
  locales: LocaleCode[];
  onOperatorChange: (operator: string) => void;
  onLocalesChange: (locales: LocaleCode[]) => void;
  validationErrors: ValidationError[];
};

const CompletenessFilter = ({
  operator,
  locales,
  onOperatorChange,
  onLocalesChange,
  validationErrors,
}: CompletenessFilterProps) => {
  const operatorErrors = filterErrors(validationErrors, '[operator]');
  const localesErrors = filterErrors(validationErrors, '[context][locales]');

  return (
    <Container>
      <OperatorSelector operator={operator} onChange={onOperatorChange} validationErrors={operatorErrors} />
      {operator !== 'ALL' && (
        <LocalesSelector locales={locales} onChange={onLocalesChange} validationErrors={localesErrors} />
      )}
    </Container>
  );
};

export {CompletenessFilter};
export type {Operator};
