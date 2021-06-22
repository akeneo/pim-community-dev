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
    channel: ChannelCode | null;
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
  const availableLocales = getLocalesFromChannel(availableChannels, filter.context.channel);
  const formattedValidationErrors = formatParameters(validationErrors);
  const operatorErrors = filterErrors(formattedValidationErrors, '[operator]');
  const channelErrors = filterErrors(formattedValidationErrors, '[context][channel]');
  const localesErrors = filterErrors(formattedValidationErrors, '[context][locales]');

  const handleOperatorChange = (newOperator: Operator) => {
    if (newOperator !== 'ALL') {
      const newLocales = filter.context.locales ?? [];
      const newChannel = filter.context.channel ?? availableChannels[0].code;
      onChange({...filter, operator: newOperator, context: {locales: newLocales, channel: newChannel}});
    } else {
      onChange({...filter, operator: newOperator, context: {locales: [], channel: null}});
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
    const newFilter = {...filter, context: {channel: newChannel, locales: newLocales}};
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
            value={filter.context.channel ?? ''}
            channels={availableChannels ?? []}
            validationErrors={channelErrors}
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
