import React from 'react';
import {Button, Field, Helper, NumberInput, SectionTitle, TextInput, useBooleanState} from 'akeneo-design-system';
import {getLabel, Locale, LocaleCode, useTranslate} from '@akeneo-pim-community/shared';
import {ColumnCode, ColumnValidation, SelectColumnDefinition, SelectOption} from '../models/TableConfiguration';
import styled from 'styled-components';
import {ColumnDefinitionWithId} from './TableStructureApp';
import {Checkbox} from '@akeneo-pim-community/connectivity-connection/src/common';
import {ManageOptionsModal} from './ManageOptionsModal';
import {Attribute} from '../models/Attribute';
import {FieldsList} from '../shared/FieldsList';

const ManageOptionsButtonContainer = styled.div`
  text-align: right;
`;

type ColumnDefinitionPropertiesProps = {
  attribute: Attribute;
  selectedColumn: ColumnDefinitionWithId;
  catalogLocaleCode: LocaleCode;
  activeLocales: Locale[];
  onChange: (column: ColumnDefinitionWithId) => void;
  savedColumnIds: string[];
  isDuplicateColumnCode: (code: ColumnCode) => boolean;
};

const ColumnDefinitionProperties: React.FC<ColumnDefinitionPropertiesProps> = ({
  attribute,
  selectedColumn,
  catalogLocaleCode,
  activeLocales,
  onChange,
  savedColumnIds,
  isDuplicateColumnCode,
}) => {
  const translate = useTranslate();
  const [isManageOptionsOpen, openManageOption, closeManageOption] = useBooleanState();
  const handleValidationChange = (validation: ColumnValidation) => {
    selectedColumn.validations = {...selectedColumn.validations, ...validation};
    onChange(selectedColumn);
  };

  const handleLabelChange = (localeCode: LocaleCode, newValue: string) => {
    selectedColumn.labels[localeCode] = newValue;
    onChange(selectedColumn);
  };

  const handleCodeChange = (code: ColumnCode) => {
    selectedColumn.code = code;
    onChange(selectedColumn);
  };

  const handleManageOptionChange = (options: SelectOption[]) => {
    (selectedColumn as SelectColumnDefinition).options = options;
    onChange(selectedColumn);
  };

  const isMinGreaterThanMax =
    selectedColumn.data_type === 'number' &&
    'undefined' !== typeof selectedColumn.validations.min &&
    'undefined' !== typeof selectedColumn.validations.max &&
    selectedColumn.validations.min > selectedColumn.validations.max;

  const validations = (
    <>
      {selectedColumn.data_type === 'text' && (
        <Field label={translate('pim_table_attribute.validations.max_length')}>
          <NumberInput
            value={`${selectedColumn.validations.max_length}`}
            onChange={value => handleValidationChange({max_length: parseInt(value)})}
            min={1}
            max={100}
            step={1}
          />
        </Field>
      )}
      {selectedColumn.data_type === 'number' && (
        <>
          <Field label={translate('pim_table_attribute.validations.min')}>
            <NumberInput
              value={`${selectedColumn.validations.min}`}
              onChange={value => handleValidationChange({min: parseFloat(value)})}
            />
          </Field>
          <Field label={translate('pim_table_attribute.validations.max')}>
            <NumberInput
              value={`${selectedColumn.validations.max}`}
              onChange={value => handleValidationChange({max: parseFloat(value)})}
            />
            {isMinGreaterThanMax && (
              <Helper level='error'>{translate('pim_table_attribute.validations.max_greater_than_min')}</Helper>
            )}
          </Field>
          <Checkbox
            checked={selectedColumn.validations.decimals_allowed ?? false}
            onChange={event => handleValidationChange({decimals_allowed: event.target.checked})}>
            {translate('pim_table_attribute.validations.decimals_allowed')}
          </Checkbox>
        </>
      )}
      {selectedColumn.data_type === 'select' && (
        <ManageOptionsButtonContainer>
          <Button onClick={openManageOption} ghost size='small' level='tertiary'>
            {translate('pim_table_attribute.form.attribute.manage_options')}
          </Button>
          {isManageOptionsOpen && (
            <ManageOptionsModal
              attribute={attribute}
              columnDefinition={selectedColumn}
              onClose={closeManageOption}
              onChange={handleManageOptionChange}
            />
          )}
        </ManageOptionsButtonContainer>
      )}
    </>
  );

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
          requiredLabel={translate('pim_common.required_label')}>
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
              maxLength={250}
            />
          </Field>
        ))}
      </FieldsList>
    </div>
  );
};

export {ColumnDefinitionProperties};
