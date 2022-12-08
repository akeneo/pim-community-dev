import React, {useState} from 'react';
import styled from 'styled-components';
import {Collapse, Field, Helper, Pill, SelectInput} from 'akeneo-design-system';
import {
  filterErrors,
  getAllLocalesFromChannels,
  getLabel,
  Section,
  useTranslate,
  useUserContext,
  ValidationError,
} from '@akeneo-pim-community/shared';
import {useChannels, useReferenceEntityAttributes} from '../../../hooks';
import {
  isDefaultReferenceEntityCollectionSelection,
  ReferenceEntityCollectionAttributeSelection,
  ReferenceEntityCollectionSelection,
} from './model';
import {AttributeSelector} from '../ReferenceEntity/Attribute';
import {availableSeparators, isCollectionSeparator} from '../common';
import {LocaleDropdown} from '../../../components/LocaleDropdown';

const AttributeSelectorContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: 5px;
`;

type ReferenceEntityCollectionSelectorProps = {
  referenceEntityCode: string;
  selection: ReferenceEntityCollectionSelection;
  validationErrors: ValidationError[];
  onSelectionChange: (updatedSelection: ReferenceEntityCollectionSelection) => void;
};

const ReferenceEntityCollectionSelector = ({
  selection,
  referenceEntityCode,
  validationErrors,
  onSelectionChange,
}: ReferenceEntityCollectionSelectorProps) => {
  const [isSelectorCollapsed, toggleSelectorCollapse] = useState<boolean>(false);
  const translate = useTranslate();
  const channels = useChannels();
  const referenceEntityAttributes = useReferenceEntityAttributes(referenceEntityCode);
  const catalogLocale = useUserContext().get('catalogLocale');
  const locales = getAllLocalesFromChannels(channels);
  const typeErrors = filterErrors(validationErrors, '[type]');
  const localeErrors = filterErrors(validationErrors, '[locale]');
  const separatorErrors = filterErrors(validationErrors, '[separator]');

  const handleTypeChange = (type: string) => {
    if ('code' === type) {
      onSelectionChange({type: 'code', separator: selection.separator});
    } else if ('label' === type) {
      onSelectionChange({type: 'label', separator: selection.separator, locale: locales[0].code});
    } else {
      const referenceEntityAttribute = referenceEntityAttributes.find(({identifier}) => identifier === type);

      if (undefined !== referenceEntityAttribute) {
        onSelectionChange({
          type: 'attribute',
          separator: selection.separator,
          attribute_identifier: type,
          attribute_type: referenceEntityAttribute.type,
          reference_entity_code: referenceEntityCode,
          channel: referenceEntityAttribute.value_per_channel ? channels[0].code : null,
          locale: referenceEntityAttribute.value_per_locale ? locales[0].code : null,
        });
      }
    }
  };

  const selectedReferenceEntityAttribute =
    'attribute' === selection.type
      ? referenceEntityAttributes.find(attribute => selection.attribute_identifier === attribute.identifier) ?? null
      : null;

  return (
    <Collapse
      collapseButtonLabel={isSelectorCollapsed ? translate('pim_common.close') : translate('pim_common.open')}
      label={
        <>
          {translate('akeneo.tailored_export.column_details.sources.selection.title')}
          {0 === validationErrors.length && !isDefaultReferenceEntityCollectionSelection(selection) && (
            <Pill level="primary" />
          )}
          {0 < validationErrors.length && <Pill level="danger" />}
        </>
      }
      isOpen={isSelectorCollapsed}
      onCollapse={toggleSelectorCollapse}
    >
      <Section>
        <AttributeSelectorContainer>
          <Field label={translate('pim_common.type')}>
            <SelectInput
              clearable={false}
              invalid={0 < typeErrors.length}
              emptyResultLabel={translate('pim_common.no_result')}
              openLabel={translate('pim_common.open')}
              value={'attribute' === selection.type ? selection.attribute_identifier : selection.type}
              onChange={handleTypeChange}
            >
              <SelectInput.Option title={translate('pim_common.code')} value="code">
                {translate('pim_common.code')}
              </SelectInput.Option>
              <SelectInput.Option title={translate('pim_common.label')} value="label">
                {translate('pim_common.label')}
              </SelectInput.Option>
              {referenceEntityAttributes.map(referenceEntityAttribute => (
                <SelectInput.Option
                  key={referenceEntityAttribute.identifier}
                  title={getLabel(referenceEntityAttribute.labels, catalogLocale, referenceEntityAttribute.code)}
                  value={referenceEntityAttribute.identifier}
                >
                  {getLabel(referenceEntityAttribute.labels, catalogLocale, referenceEntityAttribute.code)}
                </SelectInput.Option>
              ))}
            </SelectInput>
            {typeErrors.map((error, index) => (
              <Helper key={index} inline={true} level="error">
                {translate(error.messageTemplate, error.parameters)}
              </Helper>
            ))}
          </Field>
          {'attribute' === selection.type && null !== selectedReferenceEntityAttribute && (
            <AttributeSelector<ReferenceEntityCollectionAttributeSelection>
              attribute={selectedReferenceEntityAttribute}
              selection={selection}
              channels={channels}
              validationErrors={validationErrors}
              onSelectionChange={onSelectionChange}
            />
          )}
        </AttributeSelectorContainer>
        {'label' === selection.type && (
          <LocaleDropdown
            value={selection.locale}
            validationErrors={localeErrors}
            locales={locales}
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

export {ReferenceEntityCollectionSelector};
