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

type Filter = {
  field: 'completeness';
  operator: Operator;
  value: number;
  context: {
    locales: LocaleCode[];
  };
};
type CompletenessFilterProps = {
  availableOperators: Operator[];
  filter: Filter;
  onFilterChange: (newFilter: Filter) => void;
  validationErrors: ValidationError[];
};

const CompletenessFilter = ({
  availableOperators,
  filter,
  onFilterChange,
  validationErrors,
}: CompletenessFilterProps) => {
  const operatorErrors = filterErrors(validationErrors, '[operator]');
  const localesErrors = filterErrors(validationErrors, '[context][locales]');

  const onOperatorChange = (newOperator: Operator) => {
    const newFilter = {...filter, operator: newOperator};
    onFilterChange(newFilter);
  };
  const onLocalesChange = (newLocales: LocaleCode[]) => {
    const newFilter = {...filter, context: {locales: newLocales}};
    onFilterChange(newFilter);
  };

  return (
    <Container>
      <OperatorSelector
        availableOperators={availableOperators}
        operator={filter.operator}
        onChange={onOperatorChange}
        validationErrors={operatorErrors}
      />
      {filter.operator !== 'ALL' && (
        <LocalesSelector locales={filter.context.locales} onChange={onLocalesChange} validationErrors={localesErrors} />
      )}
    </Container>
  );
};

export {CompletenessFilter};
export type {Operator};
