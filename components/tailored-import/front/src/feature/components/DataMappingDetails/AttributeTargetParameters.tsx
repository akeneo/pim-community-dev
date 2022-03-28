import React, {FunctionComponent} from 'react';
import styled from 'styled-components';
import {Checkbox, Helper} from 'akeneo-design-system';
import {
  ChannelCode,
  getErrorsForPath,
  getLocaleFromChannel,
  getLocalesFromChannel,
  LocaleCode,
  useTranslate,
  ValidationError,
} from '@akeneo-pim-community/shared';
import {AttributeTargetParameterConfiguratorProps, AttributeTarget, isIdentifierAttribute} from '../../models';
import {useAttribute, useChannels} from '../../hooks';
import {ChannelDropdown} from './ChannelDropdown';
import {LocaleDropdown} from './LocaleDropdown';
import {NumberConfigurator} from '../TargetDetails';

const Container = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
`;

const attributeTargetParameterConfigurators: {
  [attributeType: string]: FunctionComponent<AttributeTargetParameterConfiguratorProps>;
} = {
  pim_catalog_number: NumberConfigurator,
};

type AttributeTargetParametersProps = {
  target: AttributeTarget;
  validationErrors: ValidationError[];
  onTargetChange: (target: AttributeTarget) => void;
};

const AttributeTargetParameters = ({target, validationErrors, onTargetChange}: AttributeTargetParametersProps) => {
  const translate = useTranslate();
  const channels = useChannels();
  const [isFetching, attribute] = useAttribute(target.code);
  const locales = getLocalesFromChannel(channels, target.channel);
  const localeSpecificFilteredLocales =
    null !== attribute && attribute.is_locale_specific
      ? locales.filter(({code}) => attribute.available_locales.includes(code))
      : locales;
  const attributeErrors = getErrorsForPath(validationErrors, '');

  const handleChannelChange = (channel: ChannelCode) => {
    const locale = getLocaleFromChannel(channels, channel, target.locale);
    onTargetChange({...target, channel, locale});
  };

  const handleLocaleChange = (locale: LocaleCode) => {
    onTargetChange({...target, locale});
  };

  const handleClearIfEmptyChange = (clearIfEmpty: boolean) =>
    onTargetChange({...target, action_if_empty: clearIfEmpty ? 'clear' : 'skip'});

  // TODO: add skeleton
  if (isFetching) return null;

  if (null === attribute) {
    return (
      <>
        {attributeErrors.map((error, index) => (
          <Helper key={index} level="error">
            {translate(error.messageTemplate, error.parameters)}
          </Helper>
        ))}
      </>
    );
  }

  if (isIdentifierAttribute(attribute)) {
    return <Helper>{translate('akeneo.tailored_import.data_mapping.target.identifier')}</Helper>;
  }

  const Configurator = attributeTargetParameterConfigurators[attribute.type] ?? null;

  return (
    <Container>
      {0 < channels.length && null !== target.channel && (
        <ChannelDropdown
          value={target.channel}
          channels={channels}
          validationErrors={getErrorsForPath(validationErrors, '[channel]')}
          onChange={handleChannelChange}
        />
      )}
      {0 < localeSpecificFilteredLocales.length && null !== target.locale && (
        <LocaleDropdown
          value={target.locale}
          validationErrors={getErrorsForPath(validationErrors, '[locale]')}
          locales={localeSpecificFilteredLocales}
          onChange={handleLocaleChange}
        >
          {attribute.is_locale_specific && (
            <Helper inline={true}>{translate('akeneo.tailored_import.data_mapping.target.locale_specific')}</Helper>
          )}
        </LocaleDropdown>
      )}
      <Checkbox checked={'clear' === target.action_if_empty} onChange={handleClearIfEmptyChange}>
        {translate('akeneo.tailored_import.data_mapping.target.clear_if_empty')}
      </Checkbox>
      {null !== Configurator && (
        <Configurator
          target={target}
          attribute={attribute}
          validationErrors={validationErrors}
          onTargetAttributeChange={onTargetChange}
        />
      )}
    </Container>
  );
};

export {AttributeTargetParameters};
