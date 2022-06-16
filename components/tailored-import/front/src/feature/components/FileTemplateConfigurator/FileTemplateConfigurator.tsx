import React from 'react';
import styled from 'styled-components';
import {Helper, NumberInput, SelectInput, Field} from 'akeneo-design-system';
import {filterErrors, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {
  FileStructure,
  FileTemplateInformation,
  generateColumnName,
  getRowAtPosition,
  MAXIMUM_FIRST_PRODUCT_ROW,
  MAXIMUM_HEADER_ROW,
  MINIMUM_FIRST_PRODUCT_ROW,
  MINIMUM_HEADER_ROW,
} from '../../models';

type FileTemplateConfiguratorProps = {
  fileTemplateInformation: FileTemplateInformation;
  fileStructure: FileStructure;
  onFileStructureChange: (fileStructure: FileStructure) => void;
  onSheetChange: (sheet: string) => void;
  validationErrors: ValidationError[];
};

const FileTemplateConfiguratorContainer = styled.div`
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  grid-gap: 5rem;
`;

const FileTemplateConfigurator = ({
  fileTemplateInformation,
  fileStructure,
  onFileStructureChange,
  onSheetChange,
  validationErrors,
}: FileTemplateConfiguratorProps) => {
  const translate = useTranslate();
  const headerCells = getRowAtPosition(fileTemplateInformation, fileStructure.header_row);
  const sheetNameErrors = filterErrors(validationErrors, '[sheet_name]');
  const headerRowErrors = filterErrors(validationErrors, '[header_row]');
  const firstProductRowErrors = filterErrors(validationErrors, '[first_product_row]');
  const firstColumnErrors = filterErrors(validationErrors, '[first_column]');
  const uniqueIdentifierColumnErrors = filterErrors(validationErrors, '[unique_identifier_column]');

  const handleHeaderRowChange = (newHeaderRow: string) => {
    onFileStructureChange({
      ...fileStructure,
      header_row: parseInt(newHeaderRow),
      first_product_row:
        parseInt(newHeaderRow) >= fileStructure.first_product_row
          ? parseInt(newHeaderRow) + 1
          : fileStructure.first_product_row,
    });
  };

  const handleFirstProductRowChange = (firstProductRow: string) => {
    onFileStructureChange({...fileStructure, first_product_row: parseInt(firstProductRow)});
  };

  const handleFirstColumnChange = (firstColumn: string) => {
    onFileStructureChange({...fileStructure, first_column: parseInt(firstColumn)});
  };

  const handleColumnIdentifierChange = (uniqueIdentifierColumn: string) => {
    onFileStructureChange({...fileStructure, unique_identifier_column: parseInt(uniqueIdentifierColumn)});
  };

  return (
    <FileTemplateConfiguratorContainer>
      <Field label={translate('akeneo.tailored_import.file_structure.modal.sheet')}>
        <SelectInput
          clearable={false}
          emptyResultLabel={translate('pim_common.no_result')}
          invalid={sheetNameErrors.length > 0}
          onChange={onSheetChange}
          value={fileStructure.sheet_name}
          openLabel={translate('pim_common.open')}
        >
          {fileTemplateInformation.sheet_names.map((sheetName: string) => (
            <SelectInput.Option key={sheetName} value={sheetName} title={sheetName}>
              {sheetName}
            </SelectInput.Option>
          ))}
        </SelectInput>
        {sheetNameErrors.map((error, index) => (
          <Helper key={index} inline={true} level="error">
            {translate(error.messageTemplate, error.parameters)}
          </Helper>
        ))}
      </Field>
      <Field label={translate('akeneo.tailored_import.file_structure.modal.header_row')}>
        <NumberInput
          invalid={headerRowErrors.length > 0}
          onChange={handleHeaderRowChange}
          value={fileStructure.header_row.toString()}
          min={MINIMUM_HEADER_ROW}
          max={MAXIMUM_HEADER_ROW}
        />
        {headerRowErrors.map((error, index) => (
          <Helper key={index} inline={true} level="error">
            {translate(error.messageTemplate, error.parameters)}
          </Helper>
        ))}
      </Field>
      <Field label={translate('akeneo.tailored_import.file_structure.modal.first_product_row')}>
        <NumberInput
          invalid={firstProductRowErrors.length > 0}
          onChange={handleFirstProductRowChange}
          value={fileStructure.first_product_row.toString()}
          min={MINIMUM_FIRST_PRODUCT_ROW}
          max={MAXIMUM_FIRST_PRODUCT_ROW}
        />
        {firstProductRowErrors.map((error, index) => (
          <Helper key={index} inline={true} level="error">
            {translate(error.messageTemplate, error.parameters)}
          </Helper>
        ))}
      </Field>
      <Field label={translate('akeneo.tailored_import.file_structure.modal.first_column')}>
        <SelectInput
          invalid={firstColumnErrors.length > 0}
          readOnly={headerCells.length === 0}
          clearable={false}
          emptyResultLabel={translate('pim_common.no_result')}
          onChange={handleFirstColumnChange}
          value={fileStructure.first_column.toString()}
          openLabel={translate('pim_common.open')}
        >
          {headerCells.map((headerCell, index) => (
            <SelectInput.Option key={index} value={index.toString()} title={headerCell}>
              {generateColumnName(index, headerCell)}
            </SelectInput.Option>
          ))}
        </SelectInput>
        {firstColumnErrors.map((error, index) => (
          <Helper key={index} inline={true} level="error">
            {translate(error.messageTemplate, error.parameters)}
          </Helper>
        ))}
      </Field>
      <Field label={translate('akeneo.tailored_import.file_structure.modal.unique_identifier_column')}>
        <SelectInput
          invalid={uniqueIdentifierColumnErrors.length > 0}
          readOnly={headerCells.length === 0}
          clearable={false}
          emptyResultLabel={translate('pim_common.no_result')}
          onChange={handleColumnIdentifierChange}
          value={fileStructure.unique_identifier_column.toString()}
          openLabel={translate('pim_common.open')}
        >
          {headerCells.map((headerCell, index) => (
            <SelectInput.Option key={index} value={index.toString()} title={headerCell}>
              {generateColumnName(index, headerCell)}
            </SelectInput.Option>
          ))}
        </SelectInput>
        {uniqueIdentifierColumnErrors.map((error, index) => (
          <Helper key={index} inline={true} level="error">
            {translate(error.messageTemplate, error.parameters)}
          </Helper>
        ))}
      </Field>
    </FileTemplateConfiguratorContainer>
  );
};

export {FileTemplateConfigurator};
