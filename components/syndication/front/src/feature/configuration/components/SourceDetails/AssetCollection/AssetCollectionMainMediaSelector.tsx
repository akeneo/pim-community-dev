import React from 'react';
import {Checkbox, Field, Helper, SelectInput} from 'akeneo-design-system';
import {
  ASSET_COLLECTION_MEDIA_FILE_SELECTION_TYPE,
  ASSET_COLLECTION_MEDIA_LINK_SELECTION_TYPE,
  AssetCollectionMainMediaSelection,
  AssetCollectionSelection,
  isValidAssetCollectionMediaFileSelectionProperty,
} from './model';
import {ChannelDropdown} from '../../shared/ChannelDropdown';
import {
  ChannelCode,
  filterErrors,
  getLocaleFromChannel,
  getLocalesFromChannel,
  useTranslate,
  ValidationError,
} from '@akeneo-pim-community/shared';
import {LocaleDropdown} from '../../shared/LocaleDropdown';
import {useChannels} from '../../../hooks';

type AssetCollectionMainMediaSelectorProps = {
  selection: AssetCollectionMainMediaSelection;
  validationErrors: ValidationError[];
  onSelectionChange: (updatedSelection: AssetCollectionSelection) => void;
};

const AssetCollectionMainMediaSelector = ({
  selection,
  validationErrors,
  onSelectionChange,
}: AssetCollectionMainMediaSelectorProps) => {
  const translate = useTranslate();
  const channelErrors = filterErrors(validationErrors, '[channel]');
  const propertyErrors = filterErrors(validationErrors, '[property]');
  const localeErrors = filterErrors(validationErrors, '[locale]');

  const channels = useChannels();
  const locales = getLocalesFromChannel(channels, selection.channel);

  return (
    <>
      {ASSET_COLLECTION_MEDIA_FILE_SELECTION_TYPE === selection.type && (
        <Field label={translate('akeneo.syndication.data_mapping_details.sources.selection.asset_collection.property')}>
          <SelectInput
            clearable={false}
            invalid={0 < propertyErrors.length}
            emptyResultLabel={translate('pim_common.no_result')}
            openLabel={translate('pim_common.open')}
            value={selection.property}
            onChange={newProperty => {
              if (isValidAssetCollectionMediaFileSelectionProperty(newProperty)) {
                onSelectionChange({...selection, property: newProperty});
              }
            }}
          >
            <SelectInput.Option
              title={translate('akeneo.syndication.data_mapping_details.sources.selection.type.key')}
              value="file_key"
            >
              {translate('akeneo.syndication.data_mapping_details.sources.selection.type.key')}
            </SelectInput.Option>
            <SelectInput.Option
              title={translate('akeneo.syndication.data_mapping_details.sources.selection.type.path')}
              value="file_path"
            >
              {translate('akeneo.syndication.data_mapping_details.sources.selection.type.path')}
            </SelectInput.Option>
            <SelectInput.Option
              title={translate('akeneo.syndication.data_mapping_details.sources.selection.type.name')}
              value="original_filename"
            >
              {translate('akeneo.syndication.data_mapping_details.sources.selection.type.name')}
            </SelectInput.Option>
          </SelectInput>
          {propertyErrors.map((error, index) => (
            <Helper key={index} inline={true} level="error">
              {translate(error.messageTemplate, error.parameters)}
            </Helper>
          ))}
        </Field>
      )}
      {ASSET_COLLECTION_MEDIA_LINK_SELECTION_TYPE === selection.type && (
        <Checkbox
          checked={selection.with_prefix_and_suffix}
          onChange={newValue => {
            onSelectionChange({...selection, with_prefix_and_suffix: newValue});
          }}
        >
          {translate(
            'akeneo.syndication.data_mapping_details.sources.selection.asset_collection.with_prefix_and_suffix'
          )}
        </Checkbox>
      )}
      {null !== selection.channel && (
        <ChannelDropdown
          value={selection.channel}
          channels={channels}
          validationErrors={channelErrors}
          onChange={(channelCode: ChannelCode) => {
            const localeCode = getLocaleFromChannel(channels, channelCode, selection.locale);
            onSelectionChange({...selection, locale: localeCode, channel: channelCode});
          }}
        />
      )}
      {selection.locale !== null && (
        <LocaleDropdown
          value={selection.locale}
          validationErrors={localeErrors}
          locales={locales}
          onChange={localeCode => onSelectionChange({...selection, locale: localeCode})}
        />
      )}
    </>
  );
};

export {AssetCollectionMainMediaSelector};
