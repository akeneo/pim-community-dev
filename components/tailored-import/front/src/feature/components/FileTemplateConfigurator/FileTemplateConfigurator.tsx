import React from 'react';
import {FileStructure, generateColumnName} from '../../models';
import {FileTemplateInformation} from '../../models/FileTemplateInformation';
import {NumberInput, SelectInput, Field} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';

type FileTemplateConfiguratorProps = {
  fileTemplateInformation: FileTemplateInformation;
  fileStructure: FileStructure;
  onFileStructureChange: (fileStructure: FileStructure) => void;
  onHeaderPositionChange: (headerPosition: number) => void;
  onSheetChange: (sheet: string) => void;
};

const FileTemplateConfiguratorContainer = styled.div`
  display: flex;
  flex-direction: row;
  justify-content: space-between;
`;

const FileTemplateConfigurator = ({
  fileTemplateInformation,
  fileStructure,
  onFileStructureChange,
  onHeaderPositionChange,
  onSheetChange,
}: FileTemplateConfiguratorProps) => {
  const translate = useTranslate();
  const firstCellValue = fileTemplateInformation.header_cells[fileStructure.first_column] ?? null;
  const identifierCellValue = fileTemplateInformation.header_cells[fileStructure.column_identifier_position] ?? null;

  const handleProductPositionChange = (productPosition: string) => {
    onFileStructureChange({...fileStructure, product_line: parseInt(productPosition)});
  };

  const handleColumnPositionChange = (columnPosition: string) => {
    onFileStructureChange({...fileStructure, first_column: parseInt(columnPosition)});
  };

  const handleColumnIdentifierPositionChange = (columnIdentifierPosition: string) => {
    onFileStructureChange({...fileStructure, column_identifier_position: parseInt(columnIdentifierPosition)});
  };

  return (
    <FileTemplateConfiguratorContainer>
      <Field label={translate('akeneo.tailored_import.file_structure.modal.sheet')}>
        <SelectInput
          clearable={false}
          emptyResultLabel={translate('pim_common.no_result')}
          onChange={onSheetChange}
          value={fileStructure.sheet_name}
          openLabel={translate('pim_common.open')}
        >
          {fileTemplateInformation.sheets.map((sheetName: string) => (
            <SelectInput.Option key={sheetName} value={sheetName}>
              {sheetName}
            </SelectInput.Option>
          ))}
        </SelectInput>
      </Field>
      {/*header line vs header position to choose*/}
      <Field label={translate('akeneo.tailored_import.file_structure.modal.header_position')}>
        <NumberInput
          onChange={(headerPosition: string) => onHeaderPositionChange(parseInt(headerPosition))}
          value={fileStructure.header_line.toString()}
          min={1}
          max={500}
        />
      </Field>
      <Field label={translate('akeneo.tailored_import.file_structure.modal.product_position')}>
        <NumberInput
          onChange={handleProductPositionChange}
          value={fileStructure.product_line.toString()}
          min={2}
          max={500}
        />
      </Field>
      {/** column position vs first column */}
      <Field label={translate('akeneo.tailored_import.file_structure.modal.column_position')}>
        <SelectInput
          readOnly={fileTemplateInformation.header_cells.length === 0}
          clearable={false}
          emptyResultLabel={translate('pim_common.no_result')}
          onChange={handleColumnPositionChange}
          value={firstCellValue !== null ? generateColumnName(fileStructure.first_column, firstCellValue) : null}
          openLabel={translate('pim_common.open')}
        >
          {fileTemplateInformation.header_cells.map((headerCell, index) => (
            <SelectInput.Option key={index} value={index.toString()}>
              {generateColumnName(index, headerCell)}
            </SelectInput.Option>
          ))}
        </SelectInput>
      </Field>
      <Field label={translate('akeneo.tailored_import.file_structure.modal.column_identifier_position')}>
        <SelectInput
          readOnly={fileTemplateInformation.header_cells.length === 0}
          clearable={false}
          emptyResultLabel={translate('pim_common.no_result')}
          onChange={handleColumnIdentifierPositionChange}
          value={identifierCellValue !== null ? generateColumnName(fileStructure.column_identifier_position, identifierCellValue) : null}
          openLabel={translate('pim_common.open')}
        >
          {fileTemplateInformation.header_cells.map((headerCell, index) => (
            <SelectInput.Option key={index} value={index.toString()}>
              {generateColumnName(index, headerCell)}
            </SelectInput.Option>
          ))}
        </SelectInput>
      </Field>
    </FileTemplateConfiguratorContainer>
  );
};

export {FileTemplateConfigurator};
