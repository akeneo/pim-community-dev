import React, {useCallback, useState} from 'react';
import {Collapse, Field, Helper, Pill, SelectInput} from 'akeneo-design-system';
import {
  ChannelCode,
  getLocaleFromChannel,
  filterErrors,
  getAllLocalesFromChannels,
  Section,
  useTranslate,
  ValidationError,
  getErrorsForPath,
  getLocalesFromChannel,
} from '@akeneo-pim-community/shared';
import {useChannels, useAssetFamily} from '../../../hooks';
import {LocaleDropdown} from '../../LocaleDropdown';
import {
  AssetCollectionSelection,
  isAssetCollectionSelection,
  isAssetCollectionMediaSelection,
  isDefaultAssetCollectionSelection,
  getDefaultAssetCollectionSelection,
  getDefaultAssetCollectionMediaSelection,
  ASSET_COLLECTION_MEDIA_FILE_SELECTION_TYPE,
  isValidAssetCollectionMediaFileSelectionProperty,
} from './model';
import {ChannelDropdown} from '../../ChannelDropdown';
import {availableSeparators, isCollectionSeparator} from './model';

type AssetCollectionSelectorProps = {
  assetFamilyCode: string;
  selection: AssetCollectionSelection;
  validationErrors: ValidationError[];
  onSelectionChange: (updatedSelection: AssetCollectionSelection) => void;
};

const AssetCollectionSelector = ({
  assetFamilyCode,
  selection,
  validationErrors,
  onSelectionChange,
}: AssetCollectionSelectorProps) => {
  const [isSelectorCollapsed, toggleSelectorCollapse] = useState<boolean>(false);
  const translate = useTranslate();
  const channels = useChannels();
  const locales = getAllLocalesFromChannels(channels);
  const globalErrors = getErrorsForPath(validationErrors, '');
  const localeErrors = filterErrors(validationErrors, '[locale]');
  const channelErrors = filterErrors(validationErrors, '[channel]');
  const typeErrors = filterErrors(validationErrors, '[type]');
  const separatorErrors = filterErrors(validationErrors, '[separator]');
  const propertyErrors = filterErrors(validationErrors, '[property]');
  const assetFamily = useAssetFamily(assetFamilyCode);
  const handleSelectionTypeChange = useCallback(
    (type: string) => {
      switch (type) {
        case 'code':
          onSelectionChange({
            type,
            separator: selection.separator,
          });
          break;
        case 'label':
          onSelectionChange({
            type,
            locale: locales[0].code,
            separator: selection.separator,
          });
          break;
        case 'main_media':
          if (null === assetFamily || 0 === channels.length) {
            return;
          }
          const defaultAssetCollectionMediaSelection = getDefaultAssetCollectionMediaSelection(assetFamily, channels);
          onSelectionChange({
            ...defaultAssetCollectionMediaSelection,
            separator: selection.separator,
          });
          break;
      }
    },
    [onSelectionChange, selection.separator, locales, assetFamily, channels]
  );
  const groupedMediaSelectionType = isAssetCollectionMediaSelection(selection) ? 'main_media' : selection.type;

  return (
    <Collapse
      collapseButtonLabel={isSelectorCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
      label={
        <>
          {translate('akeneo.tailored_export.column_details.sources.selection.title')}
          {0 === validationErrors.length && !isDefaultAssetCollectionSelection(selection) && <Pill level="primary" />}
          {0 < validationErrors.length && <Pill level="danger" />}
        </>
      }
      isOpen={isSelectorCollapsed}
      onCollapse={toggleSelectorCollapse}
    >
      <Section>
        {globalErrors.map((error, index) => (
          <Helper key={index} level="error">
            {translate(error.messageTemplate, error.parameters)}
          </Helper>
        ))}
        <Field label={translate('pim_common.type')}>
          <SelectInput
            clearable={false}
            invalid={0 < typeErrors.length}
            emptyResultLabel={translate('pim_common.no_result')}
            openLabel={translate('pim_common.open')}
            value={groupedMediaSelectionType}
            onChange={handleSelectionTypeChange}
          >
            <SelectInput.Option title={translate('pim_common.label')} value="label">
              {translate('pim_common.label')}
            </SelectInput.Option>
            <SelectInput.Option title={translate('pim_common.code')} value="code">
              {translate('pim_common.code')}
            </SelectInput.Option>
            <SelectInput.Option
              title={translate('akeneo.tailored_export.column_details.sources.selection.type.main_media')}
              value="main_media"
            >
              {translate('akeneo.tailored_export.column_details.sources.selection.type.main_media')}
            </SelectInput.Option>
          </SelectInput>
          {typeErrors.map((error, index) => (
            <Helper key={index} inline={true} level="error">
              {translate(error.messageTemplate, error.parameters)}
            </Helper>
          ))}
        </Field>
        {isAssetCollectionMediaSelection(selection) && ASSET_COLLECTION_MEDIA_FILE_SELECTION_TYPE === selection.type && (
          <Field label={translate('akeneo.tailored_export.column_details.sources.selection.asset_collection.property')}>
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
                title={translate('akeneo.tailored_export.column_details.sources.selection.type.key')}
                value="file_key"
              >
                {translate('akeneo.tailored_export.column_details.sources.selection.type.key')}
              </SelectInput.Option>
              <SelectInput.Option
                title={translate('akeneo.tailored_export.column_details.sources.selection.type.path')}
                value="file_path"
              >
                {translate('akeneo.tailored_export.column_details.sources.selection.type.path')}
              </SelectInput.Option>
              <SelectInput.Option
                title={translate('akeneo.tailored_export.column_details.sources.selection.type.name')}
                value="original_filename"
              >
                {translate('akeneo.tailored_export.column_details.sources.selection.type.name')}
              </SelectInput.Option>
            </SelectInput>
            {propertyErrors.map((error, index) => (
              <Helper key={index} inline={true} level="error">
                {translate(error.messageTemplate, error.parameters)}
              </Helper>
            ))}
          </Field>
        )}
        {isAssetCollectionMediaSelection(selection) && null !== selection.channel && (
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
        {isAssetCollectionMediaSelection(selection) && selection.locale !== null && (
          <LocaleDropdown
            value={selection.locale}
            validationErrors={localeErrors}
            locales={getLocalesFromChannel(channels, selection.channel)}
            onChange={localeCode => onSelectionChange({...selection, locale: localeCode})}
          />
        )}
        {'label' === selection.type && selection.locale !== null && (
          <LocaleDropdown
            value={selection.locale}
            validationErrors={localeErrors}
            locales={locales}
            onChange={localeCode => onSelectionChange({...selection, locale: localeCode})}
          />
        )}
        <Field label={translate('akeneo.tailored_export.column_details.sources.selection.collection_separator.title')}>
          <SelectInput
            invalid={0 < separatorErrors.length}
            clearable={false}
            emptyResultLabel={translate('pim_common.no_result')}
            openLabel={translate('pim_common.open')}
            value={selection.separator}
            onChange={separator => {
              if (isCollectionSeparator(separator)) {
                onSelectionChange({...selection, separator});
              }
            }}
          >
            {Object.entries(availableSeparators).map(([separator, name]) => (
              <SelectInput.Option
                key={separator}
                title={translate(
                  `akeneo.tailored_export.column_details.sources.selection.collection_separator.${name}`
                )}
                value={separator}
              >
                {translate(`akeneo.tailored_export.column_details.sources.selection.collection_separator.${name}`)}
              </SelectInput.Option>
            ))}
          </SelectInput>
          {separatorErrors.map((error, index) => (
            <Helper key={index} inline={true} level="error">
              {translate(error.messageTemplate, error.parameters)}
            </Helper>
          ))}
        </Field>
      </Section>
    </Collapse>
  );
};

export {AssetCollectionSelector, getDefaultAssetCollectionSelection, isAssetCollectionSelection};
export type {AssetCollectionSelection};
