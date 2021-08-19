import React, {useState} from 'react';
import {Collapse, Field, Helper, SelectInput} from 'akeneo-design-system';
import {
  filterErrors,
  getAllLocalesFromChannels,
  LocaleCode,
  Section,
  useTranslate,
  ValidationError,
} from '@akeneo-pim-community/shared';
import {useChannels} from '../../../hooks';
import {LocaleDropdown} from '../../LocaleDropdown';

const availableSeparators = {',': 'comma', ';': 'semicolon', '|': 'pipe'};

type CollectionSeparator = keyof typeof availableSeparators;

type CodeLabelCollectionSelection =
  | {
      type: 'code';
      separator: CollectionSeparator;
    }
  | {
      type: 'label';
      locale: LocaleCode;
      separator: CollectionSeparator;
    };

const isCollectionSeparator = (separator: unknown): separator is CollectionSeparator =>
  typeof separator === 'string' && separator in availableSeparators;

const isCodeLabelCollectionSelection = (selection: any): selection is CodeLabelCollectionSelection =>
  'type' in selection &&
  (selection.type === 'code' || (selection.type === 'label' && 'locale' in selection)) &&
  'separator' in selection &&
  isCollectionSeparator(selection.separator);

type CodeLabelCollectionSelectorProps = {
  selection: CodeLabelCollectionSelection;
  validationErrors: ValidationError[];
  onSelectionChange: (updatedSelection: CodeLabelCollectionSelection) => void;
};

const CodeLabelCollectionSelector = ({
  selection,
  validationErrors,
  onSelectionChange,
}: CodeLabelCollectionSelectorProps) => {
  const [isSelectorCollapsed, toggleSelectorCollapse] = useState<boolean>(true);
  const translate = useTranslate();
  const channels = useChannels();
  const locales = getAllLocalesFromChannels(channels);
  const separatorErrors = filterErrors(validationErrors, '[separator]');
  const localeErrors = filterErrors(validationErrors, '[locale]');
  const typeErrors = filterErrors(validationErrors, '[type]');

  return (
    <Collapse
      collapseButtonLabel={isSelectorCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
      label={translate('akeneo.tailored_export.column_details.sources.selection.title')}
      isOpen={isSelectorCollapsed}
      onCollapse={toggleSelectorCollapse}
    >
      <Section>
        <Field label={translate('pim_common.type')}>
          <SelectInput
            clearable={false}
            emptyResultLabel={translate('pim_common.no_result')}
            openLabel={translate('pim_common.open')}
            value={selection.type}
            invalid={0 < typeErrors.length}
            onChange={type => {
              if ('label' === type) {
                onSelectionChange({type, locale: locales[0].code, separator: selection.separator});
              } else if ('code' === type) {
                onSelectionChange({type, separator: selection.separator});
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
        {'label' === selection.type && (
          <LocaleDropdown
            locales={locales}
            value={selection.locale}
            validationErrors={localeErrors}
            onChange={updatedValue => onSelectionChange({...selection, locale: updatedValue})}
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

export {CodeLabelCollectionSelector, isCodeLabelCollectionSelection, isCollectionSeparator, availableSeparators};
export type {CodeLabelCollectionSelection};
