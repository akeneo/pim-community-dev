import React from 'react';
import styled from 'styled-components';
import {
  ChannelCode,
  filterErrors,
  formatParameters,
  getLocalesFromChannel,
  LocaleCode,
  ValidationError,
  Locale,
} from '@akeneo-pim-community/shared';
import {Operator, OperatorSelector} from './OperatorSelector';
import {LocalesSelector} from './LocalesSelector';
import {ChannelDropdown} from '../ChannelDropdown';
import {useChannels} from '../../hooks';

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
    scope: ChannelCode | null;
  };
};
type CompletenessFilterProps = {
  availableOperators: Operator[];
  filter: Filter;
  onChange: (newFilter: Filter) => void;
  validationErrors: ValidationError[];
};

const CompletenessFilter = ({availableOperators, filter, onChange, validationErrors}: CompletenessFilterProps) => {
  const availableChannels = useChannels();
  const availableLocales = getLocalesFromChannel(availableChannels, filter.context.scope);
  const formattedValidationErrors = formatParameters(validationErrors);
  const operatorErrors = filterErrors(formattedValidationErrors, '[operator]');
  const scopeErrors = filterErrors(formattedValidationErrors, '[context][scope]');
  const localesErrors = filterErrors(formattedValidationErrors, '[context][locales]');

  const handleOperatorChange = (newOperator: Operator) => {
    if (newOperator !== 'ALL') {
      const newLocales = filter.context.locales ?? [];
      const newScope = filter.context.scope ?? availableChannels[0].code;
      onChange({...filter, operator: newOperator, context: {locales: newLocales, scope: newScope}});
    } else {
      onChange({...filter, operator: newOperator, context: {locales: [], scope: null}});
    }
  };
  const handleLocalesChange = (newLocales: LocaleCode[]) => {
    const newFilter = {...filter, context: {...filter.context, locales: newLocales}};
    onChange(newFilter);
  };
  const handleChannelChange = (newChannel: ChannelCode) => {
    const newAvailableLocaleCodes = getLocalesFromChannel(availableChannels, newChannel).map(
      (locale: Locale) => locale.code
    );
    const newLocales = filter.context.locales.filter((localeCode: LocaleCode) =>
      newAvailableLocaleCodes.includes(localeCode)
    );
    const newFilter = {...filter, context: {scope: newChannel, locales: newLocales}};
    onChange(newFilter);
  };

  return (
    <Container>
      <OperatorSelector
        availableOperators={availableOperators}
        operator={filter.operator}
        onChange={handleOperatorChange}
        validationErrors={operatorErrors}
      />
      {filter.operator !== 'ALL' && (
        <>
          <ChannelDropdown
            value={filter.context.scope ?? ''}
            channels={availableChannels ?? []}
            validationErrors={scopeErrors}
            onChange={handleChannelChange}
          />
          <LocalesSelector
            value={filter.context.locales ?? []}
            locales={availableLocales ?? []}
            onChange={handleLocalesChange}
            validationErrors={localesErrors}
          />
        </>
      )}
    </Container>
  );
};

export {CompletenessFilter};
export type {Operator};
