import React, {useState} from 'react';
import {Collapse, Field, Helper, SelectInput} from 'akeneo-design-system';
import {
  filterErrors,
  getAllLocalesFromChannels,
  Section,
  useTranslate,
  ValidationError,
} from '@akeneo-pim-community/shared';
import {useChannels} from '../../../hooks';
import {LocaleDropdown} from '../../LocaleDropdown';
import {
  availableSeparators,
  isCollectionSeparator,
  isEntityType,
  isProductOrProductModelSelection,
  SimpleAssociationTypeSelection,
} from './model';
import {ChannelDropdown} from '../../ChannelDropdown';

type SimpleAssociationTypeSelectorProps = {
  selection: SimpleAssociationTypeSelection;
  validationErrors: ValidationError[];
  onSelectionChange: (updatedSelection: SimpleAssociationTypeSelection) => void;
};

const SimpleAssociationTypeSelector = ({
  selection,
  validationErrors,
  onSelectionChange,
}: SimpleAssociationTypeSelectorProps) => {
  const [isSelectorCollapsed, toggleSelectorCollapse] = useState<boolean>(true);
  const translate = useTranslate();
  const channels = useChannels();
  const locales = getAllLocalesFromChannels(channels);
  const separatorErrors = filterErrors(validationErrors, '[separator]');
  const localeErrors = filterErrors(validationErrors, '[locale]');
  const channelErrors = filterErrors(validationErrors, '[channel]');
  const typeErrors = filterErrors(validationErrors, '[type]');
  const entityTypeErrors = filterErrors(validationErrors, '[entity_type]');

  return (
    <Collapse
      collapseButtonLabel={isSelectorCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
      label={translate('akeneo.tailored_export.column_details.sources.selection.title')}
      isOpen={isSelectorCollapsed}
      onCollapse={toggleSelectorCollapse}
    >
      <Section>
        <Field label={translate('akeneo.tailored_export.column_details.sources.selection.association.entity_type')}>
          <SelectInput
            clearable={false}
            emptyResultLabel={translate('pim_common.no_result')}
            openLabel={translate('pim_common.open')}
            value={selection.entity_type}
            invalid={0 < entityTypeErrors.length}
            onChange={entityType => {
              if (isEntityType(entityType)) {
                if (selection.type === 'code') {
                  onSelectionChange({type: selection.type, entity_type: entityType, separator: selection.separator});
                } else if (entityType === 'groups') {
                  onSelectionChange({
                    type: selection.type,
                    entity_type: entityType,
                    separator: selection.separator,
                    locale: selection.locale,
                  });
                } else {
                  const channel = isProductOrProductModelSelection(selection) ? selection.channel : channels[0].code;
                  onSelectionChange({
                    type: selection.type,
                    entity_type: entityType,
                    separator: selection.separator,
                    locale: selection.locale,
                    channel,
                  });
                }
              }
            }}
          >
            <SelectInput.Option title={translate('pim_common.products')} value="products">
              {translate('pim_common.products')}
            </SelectInput.Option>
            <SelectInput.Option title={translate('pim_common.product_models')} value="product_models">
              {translate('pim_common.product_models')}
            </SelectInput.Option>
            <SelectInput.Option title={translate('pim_common.groups')} value="groups">
              {translate('pim_common.groups')}
            </SelectInput.Option>
          </SelectInput>
          {entityTypeErrors.map((error, index) => (
            <Helper key={index} inline={true} level="error">
              {translate(error.messageTemplate, error.parameters)}
            </Helper>
          ))}
        </Field>
        <Field label={translate('pim_common.type')}>
          <SelectInput
            clearable={false}
            emptyResultLabel={translate('pim_common.no_result')}
            openLabel={translate('pim_common.open')}
            value={selection.type}
            invalid={0 < typeErrors.length}
            onChange={type => {
              if ('label' === type) {
                if (isProductOrProductModelSelection(selection)) {
                  onSelectionChange({...selection, type, locale: locales[0].code, channel: channels[0].code});

                  return;
                }

                onSelectionChange({
                  entity_type: 'groups',
                  separator: selection.separator,
                  type,
                  locale: locales[0].code,
                });
              } else if ('code' === type) {
                onSelectionChange({type, separator: selection.separator, entity_type: selection.entity_type});
              }
            }}
          >
            <SelectInput.Option title={translate('pim_common.label')} value="label">
              {translate('pim_common.label')}
            </SelectInput.Option>
            <SelectInput.Option title={translate('pim_common.code')} value="code">
              {translate('pim_common.code')}
            </SelectInput.Option>
          </SelectInput>
          {typeErrors.map((error, index) => (
            <Helper key={index} inline={true} level="error">
              {translate(error.messageTemplate, error.parameters)}
            </Helper>
          ))}
        </Field>
        {'label' === selection.type && isProductOrProductModelSelection(selection) && (
          <ChannelDropdown
            channels={channels}
            value={selection.channel}
            validationErrors={channelErrors}
            onChange={updatedValue => onSelectionChange({...selection, channel: updatedValue})}
          >
            <Helper inline={true} level="info">
              {translate('akeneo.tailored_export.column_details.sources.selection.association.information.channel')}
            </Helper>
          </ChannelDropdown>
        )}
        {'label' === selection.type && (
          <LocaleDropdown
            locales={locales}
            value={selection.locale}
            validationErrors={localeErrors}
            onChange={updatedValue => onSelectionChange({...selection, locale: updatedValue})}
          >
            {isProductOrProductModelSelection(selection) && (
              <Helper inline={true} level="info">
                {translate('akeneo.tailored_export.column_details.sources.selection.association.information.locale')}
              </Helper>
            )}
          </LocaleDropdown>
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

export {SimpleAssociationTypeSelector};
