import React from 'react';
import {ArrowIcon, Field, Helper, Locale, SelectInput} from 'akeneo-design-system';
import {filterErrors, getAllLocalesFromChannels, useTranslate} from '@akeneo-pim-community/shared';
import {AttributeSelectorProps} from './AttributeSelector';
import {isReferenceEntityOptionCollectionAttributeSelection, ReferenceEntityAttributeSelection} from '../model';
import {InnerField, SubField, SubFields} from './common';
import {availableSeparators, isCollectionSeparator} from '../../common';
import {isReferenceEntityCollectionOptionCollectionAttributeSelection} from '../../ReferenceEntityCollection/model';

const OptionCollectionAttributeSelector = <SelectionType extends ReferenceEntityAttributeSelection>({
  selection,
  channels,
  onSelectionChange,
  validationErrors,
}: AttributeSelectorProps<SelectionType>) => {
  const translate = useTranslate();
  const locales = getAllLocalesFromChannels(channels);
  const typeErrors = filterErrors(validationErrors, '[type]');
  const localeErrors = filterErrors(validationErrors, '[locale]');
  const separatorErrors = filterErrors(validationErrors, '[separator]');

  if (
    !isReferenceEntityOptionCollectionAttributeSelection(selection) &&
    !isReferenceEntityCollectionOptionCollectionAttributeSelection(selection)
  ) {
    throw new Error('Invalid selection type for Option Collection Attribute Selector');
  }

  return (
    <>
      <SubFields>
        <Field
          label={translate('akeneo.tailored_export.column_details.sources.selection.reference_entity.option_type')}
        >
          <SelectInput
            clearable={false}
            invalid={0 < typeErrors.length}
            emptyResultLabel={translate('pim_common.no_result')}
            openLabel={translate('pim_common.open')}
            value={selection.option_selection.type}
            onChange={type => {
              if ('label' === type) {
                onSelectionChange({
                  ...selection,
                  option_selection: {
                    type: 'label',
                    separator: selection.option_selection.separator ?? ',',
                    locale: locales[0].code,
                  },
                });
              } else if ('code' === type) {
                onSelectionChange({
                  ...selection,
                  option_selection: {type: 'code', separator: selection.option_selection.separator ?? ','},
                });
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
        {'label' === selection.option_selection.type && (
          <SubField>
            <ArrowIcon />
            <InnerField>
              <SelectInput
                invalid={0 < localeErrors.length}
                clearable={false}
                emptyResultLabel={translate('pim_common.no_result')}
                openLabel={translate('pim_common.open')}
                value={selection.option_selection.locale}
                onChange={updatedValue =>
                  onSelectionChange({
                    ...selection,
                    option_selection: {...selection.option_selection, locale: updatedValue},
                  })
                }
              >
                {locales.map(locale => (
                  <SelectInput.Option key={locale.code} title={locale.label} value={locale.code}>
                    <Locale code={locale.code} languageLabel={locale.label} />
                  </SelectInput.Option>
                ))}
              </SelectInput>
              {localeErrors.map((error, index) => (
                <Helper key={index} inline={true} level="error">
                  {translate(error.messageTemplate, error.parameters)}
                </Helper>
              ))}
            </InnerField>
          </SubField>
        )}
      </SubFields>
      <Field
        label={translate('akeneo.tailored_export.column_details.sources.selection.reference_entity.option_separator')}
      >
        <SelectInput
          invalid={0 < separatorErrors.length}
          clearable={false}
          emptyResultLabel={translate('pim_common.no_result')}
          openLabel={translate('pim_common.open')}
          value={selection.option_selection.separator}
          onChange={separator => {
            if (isCollectionSeparator(separator)) {
              onSelectionChange({...selection, option_selection: {...selection.option_selection, separator}});
            }
          }}
        >
          {Object.entries(availableSeparators).map(([separator, name]) => (
            <SelectInput.Option
              key={separator}
              title={translate(`akeneo.tailored_export.column_details.sources.selection.collection_separator.${name}`)}
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
    </>
  );
};

export {OptionCollectionAttributeSelector};
