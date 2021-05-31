import {Source} from '../../models';
import React from 'react';
import {
  getLabel,
  useTranslate,
  useUserContext,
  Locale as LocaleModel,
  getLocalesFromChannel,
  getLocaleFromChannel,
} from '@akeneo-pim-community/shared';
import {useChannels} from '../../hooks';
import {Field, Locale, SelectInput} from 'akeneo-design-system';
import styled from 'styled-components';
import {SourceDetailsPlaceholder} from './SourceDetailsPlaceholder';

const Container = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
  padding: 20px 0;
  flex: 1;
`;

type SourceConfiguratorProps = {
  source: Source;
  onSourceChange: (updatedSource: Source) => void;
};

const SourceConfigurator = ({source, onSourceChange}: SourceConfiguratorProps) => {
  const translate = useTranslate();
  const userContext = useUserContext();
  const channels = useChannels();
  const locales = getLocalesFromChannel(channels, source.channel);

  return (
    <Container>
      {null !== source.channel && (
        <Field label={translate('pim_common.channel')}>
          <SelectInput
            clearable={false}
            emptyResultLabel={translate('pim_common.no_result')}
            openLabel={translate('pim_common.open')}
            value={source.channel}
            onChange={channelCode => {
              /* istanbul ignore next: onChange cannot be called with null when clearable is false */
              if (channelCode === null) return;

              const localeCode = getLocaleFromChannel(channels, channelCode, source.locale);
              onSourceChange({...source, locale: localeCode, channel: channelCode});
            }}
          >
            {channels.map(channel => (
              <SelectInput.Option
                key={channel.code}
                title={getLabel(channel.labels, userContext.get('catalogLocale'), channel.code)}
                value={channel.code}
              >
                {getLabel(channel.labels, userContext.get('catalogLocale'), channel.code)}
              </SelectInput.Option>
            ))}
          </SelectInput>
          {/* {[].map((error, index) => (
            <Helper key={index} inline={true} level="error">
              {translate(error.messageTemplate, error.parameters)}
            </Helper>
          ))} */}
        </Field>
      )}
      {null !== source.locale && (
        <Field label={translate('pim_common.locale')}>
          <SelectInput
            clearable={false}
            emptyResultLabel={translate('pim_common.no_result')}
            openLabel={translate('pim_common.open')}
            value={source.locale}
            onChange={locale => {
              onSourceChange({...source, locale});
            }}
          >
            {locales.map((locale: LocaleModel) => (
              <SelectInput.Option key={locale.code} title={locale.label} value={locale.code}>
                <Locale code={locale.code} languageLabel={locale.label} />
              </SelectInput.Option>
            ))}
          </SelectInput>
          {/* {[].map((error, index) => (
            <Helper key={index} inline={true} level="error">
              {translate(error.messageTemplate, error.parameters)}
            </Helper>
          ))} */}
        </Field>
      )}
      {null === source.channel && null === source.locale && <SourceDetailsPlaceholder />}
    </Container>
  );
};

export {SourceConfigurator};
