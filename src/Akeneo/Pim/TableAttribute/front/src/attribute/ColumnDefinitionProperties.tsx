import React from 'react';
import {Field, NumberInput, SectionTitle, TextInput} from 'akeneo-design-system';
import {getLabel, Locale, LocaleCode, useTranslate} from '@akeneo-pim-community/shared';
import {ColumnDefinition, ColumnValidation} from '../models/TableConfiguration';
import styled from 'styled-components';

const FieldsList = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
  margin: 20px 0;
`;

type ColumnDefinitionPropertiesProps = {
  selectedColumn: ColumnDefinition;
  catalogLocaleCode: LocaleCode;
  activeLocales: Locale[];
  onChange: (column: ColumnDefinition) => void;
};

const ColumnDefinitionProperties: React.FC<ColumnDefinitionPropertiesProps> = ({
  selectedColumn,
  catalogLocaleCode,
  activeLocales,
  onChange,
}) => {
  const translate = useTranslate();

  const handleValidationChange = (validation: ColumnValidation) => {
    selectedColumn.validations = {...selectedColumn.validations, ...validation};
    onChange(selectedColumn);
  };

  const handleLabelChange = (localeCode: LocaleCode, newValue: string) => {
    selectedColumn.labels[localeCode] = newValue;
    onChange(selectedColumn);
  };

  const validations = (
    <>
      {selectedColumn.data_type === 'text' && (
        <Field label={translate('pim_table_attribute.form.validations.max_length')}>
          <NumberInput
            value={`${selectedColumn.validations.max_length}`}
            onChange={value => handleValidationChange({max_length: parseInt(value)})}
            min={0}
          />
        </Field>
      )}
    </>
  );

  return (
    <div>
      <SectionTitle title={getLabel(selectedColumn.labels, catalogLocaleCode, selectedColumn.code)}>
        <SectionTitle.Title>
          {getLabel(selectedColumn.labels, catalogLocaleCode, selectedColumn.code)}
        </SectionTitle.Title>
      </SectionTitle>
      <FieldsList>
        <Field label={translate('pim_common.code')} requiredLabel={translate('pim_common.required_label')}>
          <TextInput readOnly={true} value={selectedColumn.code} />
        </Field>
        <Field
          label={translate('pim_table_attribute.form.attribute.data_type')}
          requiredLabel={translate('pim_common.required_label')}>
          <TextInput
            readOnly={true}
            value={translate(`pim_table_attribute.properties.data_type.${selectedColumn.data_type}`)}
          />
        </Field>
        {validations}
      </FieldsList>
      <SectionTitle title={translate('pim_table_attribute.form.attribute.labels')}>
        <SectionTitle.Title>{translate('pim_table_attribute.form.attribute.labels')}</SectionTitle.Title>
      </SectionTitle>
      <FieldsList>
        {activeLocales.map(locale => (
          <Field label={locale.label} key={locale.code} locale={locale.code}>
            <TextInput
              onChange={label => handleLabelChange(locale.code, label)}
              value={selectedColumn.labels[locale.code] ?? ''}
            />
          </Field>
        ))}
      </FieldsList>
    </div>
  );
};

export {ColumnDefinitionProperties};
