import React from 'react';
import {Checkbox, Field, Helper, SectionTitle, TextInput, Link} from 'akeneo-design-system';
import {getLabel, Locale, LocaleCode, useTranslate} from '@akeneo-pim-community/shared';
import {ColumnCode, ColumnDefinition, TableAttribute} from '../models';
import {ColumnDefinitionWithId} from './TableStructureApp';
import {FieldsList} from '../shared';
import {ColumnDefinitionPropertiesMapping} from './ColumDefinitionProperties';

type ColumnDefinitionPropertiesProps = {
  attribute: TableAttribute;
  selectedColumn: ColumnDefinitionWithId;
  catalogLocaleCode: LocaleCode;
  activeLocales: Locale[];
  onChange: (column: ColumnDefinitionWithId) => void;
  savedColumnIds: string[];
  isDuplicateColumnCode: (code: ColumnCode) => boolean;
  columnDefinitionPropertiesMapping: ColumnDefinitionPropertiesMapping;
};

const ColumnDefinitionProperties: React.FC<ColumnDefinitionPropertiesProps> = ({
  attribute,
  selectedColumn,
  catalogLocaleCode,
  activeLocales,
  onChange,
  savedColumnIds,
  isDuplicateColumnCode,
  columnDefinitionPropertiesMapping,
}) => {
  const translate = useTranslate();

  const handleLabelChange = (localeCode: LocaleCode, newValue: string) => {
    selectedColumn.labels[localeCode] = newValue;
    onChange(selectedColumn);
  };

  const handleCodeChange = (code: ColumnCode) => {
    selectedColumn.code = code;
    onChange(selectedColumn);
  };

  const handleChange = (selectedColumn: ColumnDefinition) => {
    onChange(selectedColumn as ColumnDefinitionWithId);
  };

  const handleRequiredForCompleteness = (checked: boolean) => {
    onChange({...selectedColumn, is_required_for_completeness: checked});
  };

  const specificProperties = () => {
    const TypeSpecificProperties = columnDefinitionPropertiesMapping[selectedColumn.data_type]?.default;
    return (
      TypeSpecificProperties && (
        <TypeSpecificProperties selectedColumn={selectedColumn} attribute={attribute} handleChange={handleChange} />
      )
    );
  };

  const codeViolations: string[] = [];
  if (selectedColumn.code === '')
    codeViolations.push(translate('pim_table_attribute.validations.column_code_must_be_filled'));
  if (selectedColumn.code !== '' && !/^[a-zA-Z0-9_]+$/.exec(selectedColumn.code))
    codeViolations.push(translate('pim_table_attribute.validations.invalid_column_code'));
  if (isDuplicateColumnCode(selectedColumn.code)) {
    codeViolations.push(
      translate('pim_table_attribute.validations.duplicated_column_code', {
        duplicateCode: selectedColumn.code,
      })
    );
  }
  const isFirstColumn = attribute.table_configuration && attribute.table_configuration[0].code === selectedColumn.code;

  return (
    <div>
      <SectionTitle title={getLabel(selectedColumn.labels, catalogLocaleCode, selectedColumn.code)}>
        <SectionTitle.Title>
          {getLabel(selectedColumn.labels, catalogLocaleCode, selectedColumn.code)}
        </SectionTitle.Title>
      </SectionTitle>
      <FieldsList>
        <Field
          label={translate('pim_table_attribute.form.attribute.column_code')}
          requiredLabel={translate('pim_common.required_label')}
        >
          <TextInput
            readOnly={savedColumnIds.includes(selectedColumn.id)}
            value={selectedColumn.code}
            onChange={handleCodeChange}
          />
          {codeViolations.map((violation, i) => (
            <Helper key={i} level='error'>
              {violation}
            </Helper>
          ))}
        </Field>
        <Field
          label={translate('pim_table_attribute.form.attribute.data_type')}
          requiredLabel={translate('pim_common.required_label')}
        >
          <TextInput
            readOnly={true}
            value={translate(`pim_table_attribute.properties.data_type.${selectedColumn.data_type}`)}
          />
        </Field>
        {specificProperties()}
      </FieldsList>
      <SectionTitle title={translate(`pim_table_attribute.form.attribute.completeness`)}>
        <SectionTitle.Title>{translate(`pim_table_attribute.form.attribute.completeness`)}</SectionTitle.Title>
      </SectionTitle>
      <Helper level='info'>
        {translate('pim_table_attribute.form.attribute.completeness_helper_text')}{' '}
        <Link
          href='https://help.akeneo.com/pim/serenity/articles/manage-multidimensional-data-in-a-table.html#what-about-the-completeness'
          target='_blank'
        >
          {translate('pim_table_attribute.form.attribute.completeness_helper_link')}
        </Link>
      </Helper>
      <FieldsList>
        <Checkbox
          checked={isFirstColumn || !!selectedColumn.is_required_for_completeness}
          onChange={handleRequiredForCompleteness}
          readOnly={isFirstColumn}
        >
          {translate(`pim_table_attribute.form.attribute.required_for_completeness`)}
        </Checkbox>
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
              maxLength={250}
            />
          </Field>
        ))}
      </FieldsList>
    </div>
  );
};

export {ColumnDefinitionProperties};
