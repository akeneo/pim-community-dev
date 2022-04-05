import React, {ReactNode} from 'react';
import styled from 'styled-components';
import {Helper, SectionTitle} from 'akeneo-design-system';
import {
  ChannelCode,
  filterErrors,
  getErrorsForPath,
  getLocaleFromChannel,
  getLocalesFromChannel,
  LocaleCode,
  useTranslate,
  ValidationError,
} from '@akeneo-pim-community/shared';
import {AttributeTarget, Attribute, isIdentifierAttribute} from '../../models';
import {useChannels} from '../../hooks';
import {ChannelDropdown} from './ChannelDropdown';
import {LocaleDropdown} from './LocaleDropdown';

const Container = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
  margin-top: 10px;
`;

const TargetParametersContainer = styled.div`
  display: flex;
  flex-direction: column;
`;

type AttributeTargetParametersProps = {
  attribute: Attribute;
  target: AttributeTarget;
  validationErrors: ValidationError[];
  /** Specific parameters of the attribute */
  children?: ReactNode;
  onTargetChange: (target: AttributeTarget) => void;
};

const AttributeTargetParameters = ({
  attribute,
  children,
  target,
  validationErrors,
  onTargetChange,
}: AttributeTargetParametersProps) => {
  const translate = useTranslate();
  const channels = useChannels();
  const locales = getLocalesFromChannel(channels, target.channel);
  const localeSpecificFilteredLocales = attribute.is_locale_specific
    ? locales.filter(({code}) => attribute.available_locales.includes(code))
    : locales;
  const codeErrors = filterErrors(validationErrors, '[code]');

  const handleChannelChange = (channel: ChannelCode) => {
    const locale = getLocaleFromChannel(channels, channel, target.locale);
    onTargetChange({...target, channel, locale});
  };

  const handleLocaleChange = (locale: LocaleCode) => {
    onTargetChange({...target, locale});
  };

  return (
    <TargetParametersContainer>
      <SectionTitle sticky={0}>
        <SectionTitle.Title level="secondary">
          {translate('akeneo.tailored_import.data_mapping.target.title')}
        </SectionTitle.Title>
      </SectionTitle>
      {codeErrors.map((error, index) => (
        <Helper key={index} level="error">
          {translate(error.messageTemplate, error.parameters)}
        </Helper>
      ))}
      {isIdentifierAttribute(attribute) && (
        <Helper>{translate('akeneo.tailored_import.data_mapping.target.identifier')}</Helper>
      )}
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
        {children}
      </Container>
    </TargetParametersContainer>
  );
};

export type {AttributeTargetParametersProps};
export {AttributeTargetParameters};
