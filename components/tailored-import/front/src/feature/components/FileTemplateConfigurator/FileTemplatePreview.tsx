import React from 'react';
import styled from 'styled-components';
import {getColor, Placeholder, RulesIllustration, TableInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {
  FileStructure,
  FileTemplateInformation,
  generateExcelColumnLetter,
  getRowAtPosition,
  getRowsFromPosition,
} from '../../models';

const RowNumberCell = styled(TableInput.Cell)`
  min-width: 40px;
  width: 40px;
  text-align: center;
  font-weight: bold;
  color: ${getColor('grey', 140)};
  background-color: ${getColor('grey', 40)} !important;
`;

type FileTemplatePreviewProps = {
  fileTemplateInformation: FileTemplateInformation;
  fileStructure: FileStructure;
};

const FileTemplatePreview = ({fileTemplateInformation, fileStructure}: FileTemplatePreviewProps) => {
  const translate = useTranslate();
  const headerCells = getRowAtPosition(fileTemplateInformation, fileStructure.header_row, fileStructure.first_column);
  const productRows = getRowsFromPosition(
    fileTemplateInformation,
    fileStructure.first_product_row,
    fileStructure.first_column
  );

  if (0 === fileTemplateInformation.rows.length) {
    return (
      <Placeholder
        title={translate('akeneo.tailored_import.validation.file_preview.empty_sheet.title')}
        illustration={<RulesIllustration />}
        size="large"
      >
        {translate('akeneo.tailored_import.validation.file_preview.empty_sheet.description')}
      </Placeholder>
    );
  }

  return (
    <TableInput>
      <TableInput.Header>
        <RowNumberCell />
        {[...Array(headerCells.length)].map((_, index) => (
          <TableInput.HeaderCell key={index}>
            {generateExcelColumnLetter(index + fileStructure.first_column)}
          </TableInput.HeaderCell>
        ))}
      </TableInput.Header>
      <TableInput.Body>
        <TableInput.Row>
          <RowNumberCell>{isNaN(fileStructure.header_row) ? 0 : fileStructure.header_row}</RowNumberCell>
          {headerCells.map((headerCell, index) => (
            <TableInput.Cell key={index}>
              <TableInput.CellContent rowTitle={true}>{headerCell}</TableInput.CellContent>
            </TableInput.Cell>
          ))}
        </TableInput.Row>
        {productRows.map((row, rowIndex) => (
          <TableInput.Row key={rowIndex}>
            <RowNumberCell>
              {isNaN(fileStructure.first_product_row) ? 0 + rowIndex : fileStructure.first_product_row + rowIndex}
            </RowNumberCell>
            {row.map((cell, cellIndex) => (
              <TableInput.Cell key={cellIndex}>
                <TableInput.CellContent>{cell}</TableInput.CellContent>
              </TableInput.Cell>
            ))}
          </TableInput.Row>
        ))}
      </TableInput.Body>
    </TableInput>
  );
};

export {FileTemplatePreview};
