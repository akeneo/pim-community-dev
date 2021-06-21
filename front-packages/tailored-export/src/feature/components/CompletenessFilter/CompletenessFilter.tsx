import React from 'react';
import styled from 'styled-components';
import {
  filterErrors,
  LocaleCode,
  ValidationError,
  ChannelCode,
  formatParameters,
  getLocalesFromChannel,
} from '@akeneo-pim-community/shared';
import {OperatorSelector, Operator} from './OperatorSelector';
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
    channel: ChannelCode;
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
  const channel = filter.context.channel ?? availableChannels[0];
  const availableLocales = getLocalesFromChannel(availableChannels, channel);
  const formattedValidationErrors = formatParameters(validationErrors);
  const operatorErrors = filterErrors(formattedValidationErrors, '[operator]');
  const channelErrors = filterErrors(formattedValidationErrors, '[context][channel]');
  const localesErrors = filterErrors(formattedValidationErrors, '[context][locales]');

  const onOperatorChange = (newOperator: Operator) => {
    const newFilter = {...filter, operator: newOperator};
    onChange(newFilter);
  };
  const onLocalesChange = (newLocales: LocaleCode[]) => {
    const newFilter = {...filter, context: {...filter.context, locales: newLocales}};
    onChange(newFilter);
  };
  const onChannelChange = (newChannel: ChannelCode) => {
    const newFilter = {...filter, context: {...filter.context, channel: newChannel}};
    onChange(newFilter);
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
        <>
          <ChannelDropdown
            value={channel}
            channels={availableChannels}
            validationErrors={channelErrors}
            onChange={onChannelChange}
          />
          <LocalesSelector
            value={filter.context.locales}
            locales={availableLocales}
            onChange={onLocalesChange}
            validationErrors={localesErrors}
          />
        </>
      )}
    </Container>
  );
};

export {CompletenessFilter};
export type {Operator};
