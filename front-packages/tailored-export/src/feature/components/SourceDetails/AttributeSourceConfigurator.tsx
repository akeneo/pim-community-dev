import React, {FunctionComponent} from 'react';
import styled from 'styled-components';
import {Helper} from 'akeneo-design-system';
import {
  filterErrors,
  ChannelCode,
  LocaleCode,
  ValidationError,
  getLocalesFromChannel,
  getLocaleFromChannel,
  useTranslate,
} from '@akeneo-pim-community/shared';
import {useAttribute, useChannels} from '../../hooks';
import {AttributeConfiguratorProps} from '../../models';
import {ChannelDropdown} from '../ChannelDropdown';
import {LocaleDropdown} from '../LocaleDropdown';
import {Source} from '../../models/Source';
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

const Container = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
  padding: 20px 0;
  flex: 1;
`;

const configurators = {
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
} as const;

const getConfigurator = (attributeType: string): FunctionComponent<AttributeConfiguratorProps> | null => {
  return configurators[attributeType] ?? null;
};

type AttributeSourceConfiguratorProps = {
  source: Source;
  validationErrors: ValidationError[];
  onSourceChange: (updatedSource: Source) => void;
};

const AttributeSourceConfigurator = ({source, validationErrors, onSourceChange}: AttributeSourceConfiguratorProps) => {
  const translate = useTranslate();
  const channels = useChannels();
  const localeErrors = filterErrors(validationErrors, '[locale]');
  const channelErrors = filterErrors(validationErrors, '[channel]');
  const attributeErrors = filterErrors(validationErrors, '[code]');
  const locales = getLocalesFromChannel(channels, source.channel);
  const attribute = useAttribute(source.code);

  if (null === attribute) {
    if (0 === attributeErrors.length) {
      return null;
    }

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

  const localeSpecificFilteredLocales = attribute.is_locale_specific
    ? locales.filter(({code}) => attribute.available_locales.includes(code))
    : locales;

  const Configurator = getConfigurator(attribute.type);

  if (null === Configurator) {
    console.error(`No configurator found for "${attribute.type}" attribute type`);

    return null;
  }

  return (
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
            <Helper inline>{translate('akeneo.tailored_export.column_details.sources.locale_specific.info')}</Helper>
          )}
        </LocaleDropdown>
      )}
      <Configurator
        source={source}
        attribute={attribute}
        validationErrors={validationErrors}
        onSourceChange={onSourceChange}
      />
    </Container>
  );
};

export {AttributeSourceConfigurator};
