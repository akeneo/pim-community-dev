import React, {FunctionComponent} from 'react';
import styled from 'styled-components';
import {Helper} from 'akeneo-design-system';
import {
  filterErrors,
  getErrorsForPath,
  ChannelCode,
  LocaleCode,
  ValidationError,
  getLocalesFromChannel,
  getLocaleFromChannel,
  useTranslate,
} from '@akeneo-pim-community/shared';
import {useAttribute, useChannels} from '../../hooks';
import {AttributeConfiguratorProps, AttributeSource} from '../../models';
import {ChannelDropdown} from '../ChannelDropdown';
import {LocaleDropdown} from '../LocaleDropdown';
import {MeasurementConfigurator} from './Measurement/MeasurementConfigurator';
import {TextConfigurator} from './Text/TextConfigurator';
import {ReferenceEntityCollectionConfigurator} from './ReferenceEntityCollection/ReferenceEntityCollectionConfigurator';
import {FileConfigurator} from './File/FileConfigurator';
import {BooleanConfigurator} from './Boolean/BooleanConfigurator';
import {NumberConfigurator} from './Number/NumberConfigurator';
import {IdentifierConfigurator} from './Identifier/IdentifierConfigurator';
import {DateConfigurator} from './Date/DateConfigurator';
import {PriceCollectionConfigurator} from './PriceCollection/PriceCollectionConfigurator';
import {SimpleSelectConfigurator} from './SimpleSelect/SimpleSelectConfigurator';
import {MultiSelectConfigurator} from './MultiSelect/MultiSelectConfigurator';
import {ReferenceEntityConfigurator} from './ReferenceEntity/ReferenceEntityConfigurator';
import {AssetCollectionConfigurator} from './AssetCollection/AssetCollectionConfigurator';
import {ErrorBoundary, DeletedAttributeSourcePlaceholder} from './error';

const Container = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
  padding: 20px 0;
`;

const configurators: {[attributeType: string]: FunctionComponent<AttributeConfiguratorProps>} = {
  pim_catalog_text: TextConfigurator,
  pim_catalog_textarea: TextConfigurator,
  pim_catalog_metric: MeasurementConfigurator,
  akeneo_reference_entity_collection: ReferenceEntityCollectionConfigurator,
  pim_catalog_file: FileConfigurator,
  pim_catalog_image: FileConfigurator,
  pim_catalog_boolean: BooleanConfigurator,
  pim_catalog_number: NumberConfigurator,
  pim_catalog_identifier: IdentifierConfigurator,
  pim_catalog_date: DateConfigurator,
  pim_catalog_price_collection: PriceCollectionConfigurator,
  pim_catalog_simpleselect: SimpleSelectConfigurator,
  pim_catalog_multiselect: MultiSelectConfigurator,
  akeneo_reference_entity: ReferenceEntityConfigurator,
  pim_catalog_asset_collection: AssetCollectionConfigurator,
};

type AttributeSourceConfiguratorProps = {
  source: AttributeSource;
  validationErrors: ValidationError[];
  onSourceChange: (updatedSource: AttributeSource) => void;
};

const AttributeSourceConfigurator = ({source, validationErrors, onSourceChange}: AttributeSourceConfiguratorProps) => {
  const translate = useTranslate();
  const channels = useChannels();
  const localeErrors = filterErrors(validationErrors, '[locale]');
  const channelErrors = filterErrors(validationErrors, '[channel]');
  const attributeErrors = getErrorsForPath(validationErrors, '');
  const locales = getLocalesFromChannel(channels, source.channel);
  const [isFetching, attribute] = useAttribute(source.code);

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
        <DeletedAttributeSourcePlaceholder />
      </>
    );
  }

  const localeSpecificFilteredLocales = attribute.is_locale_specific
    ? locales.filter(({code}) => attribute.available_locales.includes(code))
    : locales;

  const Configurator = configurators[attribute.type] ?? null;

  if (null === Configurator) {
    console.error(`No configurator found for "${attribute.type}" attribute type`);

    return null;
  }

  return (
    <ErrorBoundary>
      {attributeErrors.map((error, index) => (
        <Helper key={index} level="error">
          {translate(error.messageTemplate, error.parameters)}
        </Helper>
      ))}
      {(null !== source.channel || null !== source.locale) && (
        <Container>
          {null !== source.channel && (
            <ChannelDropdown
              value={source.channel}
              channels={channels}
              validationErrors={channelErrors}
              onChange={(channelCode: ChannelCode) => {
                const localeCode = getLocaleFromChannel(channels, channelCode, source.locale);
                onSourceChange({...source, locale: localeCode, channel: channelCode});
              }}
            />
          )}
          {null !== source.locale && (
            <LocaleDropdown
              value={source.locale}
              validationErrors={localeErrors}
              locales={localeSpecificFilteredLocales}
              onChange={(localeCode: LocaleCode) => {
                onSourceChange({...source, locale: localeCode});
              }}
            >
              {attribute.is_locale_specific && (
                <Helper inline>{translate('akeneo.tailored_export.column_details.sources.locale_specific')}</Helper>
              )}
            </LocaleDropdown>
          )}
        </Container>
      )}
      <Configurator
        source={source}
        attribute={attribute}
        validationErrors={validationErrors}
        onSourceChange={onSourceChange}
      />
    </ErrorBoundary>
  );
};

export {AttributeSourceConfigurator};
