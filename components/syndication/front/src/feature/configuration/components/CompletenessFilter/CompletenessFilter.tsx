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
  useTranslate,
} from '@akeneo-pim-community/shared';
import {Operator, OperatorSelector} from './OperatorSelector';
import {LocalesSelector} from '../LocalesSelector';
import {ChannelDropdown} from '../shared/ChannelDropdown';
import {useChannels} from '../../hooks';

const Container = styled.div`
  display: flex;
  flex-direction: column;
  gap: 15px;
  justify-content: space-between;
  width: 100%;
  margin-top: 20px;
`;

type CompletenessFilterType = {
  field: 'completeness';
  operator: Operator;
  value: number;
  context?: {
    locales: LocaleCode[];
    scope: ChannelCode;
  };
};

const createDefaultCompletenessFilter = (): CompletenessFilterType => ({
  field: 'completeness',
  operator: 'ALL',
  value: 100,
});

type CompletenessFilterProps = {
  availableOperators: Operator[];
  filter: CompletenessFilterType;
  onChange: (newFilter: CompletenessFilterType) => void;
  validationErrors: ValidationError[];
};

const CompletenessFilter = ({availableOperators, filter, onChange, validationErrors}: CompletenessFilterProps) => {
  const translate = useTranslate();
  const availableChannels = useChannels();
  const availableLocales = getLocalesFromChannel(availableChannels, filter.context?.scope ?? null);
  const formattedValidationErrors = formatParameters(validationErrors);
  const operatorErrors = filterErrors(formattedValidationErrors, '[operator]');
  const scopeErrors = filterErrors(formattedValidationErrors, '[context][scope]');
  const localesErrors = filterErrors(formattedValidationErrors, '[context][locales]');

  const handleOperatorChange = (newOperator: Operator) => {
    if (newOperator !== 'ALL') {
      const newLocales = filter.context?.locales ?? [];
      const newScope = filter.context?.scope ?? availableChannels[0].code;
      onChange({...filter, operator: newOperator, context: {locales: newLocales, scope: newScope}});
    } else {
      onChange({field: filter.field, value: filter.value, operator: newOperator});
    }
  };
  const handleLocalesChange = (newLocales: LocaleCode[]) => {
    const newFilter = {...filter, context: {scope: filter.context?.scope ?? '', locales: newLocales}};
    onChange(newFilter);
  };

  const handleChannelChange = (newScope: ChannelCode) => {
    const newAvailableLocaleCodes = getLocalesFromChannel(availableChannels, newScope).map(
      (locale: Locale) => locale.code
    );
    const newLocales =
      filter.context?.locales.filter((localeCode: LocaleCode) => newAvailableLocaleCodes.includes(localeCode)) ?? [];
    const newFilter = {...filter, context: {scope: newScope, locales: newLocales}};
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
            value={filter.context?.scope ?? ''}
            channels={availableChannels ?? []}
            validationErrors={scopeErrors}
            onChange={handleChannelChange}
          />
          <LocalesSelector
            value={filter.context?.locales ?? []}
            locales={availableLocales ?? []}
            onChange={handleLocalesChange}
            validationErrors={localesErrors}
            label={translate('akeneo.syndication.filters.completeness.locales.label')}
            placeholder={translate('akeneo.syndication.filters.completeness.locales.placeholder')}
            removeLabel={translate('akeneo.syndication.filters.completeness.locales.remove')}
          />
        </>
      )}
    </Container>
  );
};

export {CompletenessFilter, createDefaultCompletenessFilter};
export type {Operator, CompletenessFilterType};
