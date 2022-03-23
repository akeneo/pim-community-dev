import React from 'react';
import {Placeholder, RulesIllustration, TableInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {FileStructure, FileTemplateInformation, getRowAtPosition, getRowsFromPosition} from '../../models';

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

  if (fileTemplateInformation.rows.length === 0) {
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
        {headerCells.map((headerCell, index) => (
          <TableInput.HeaderCell key={index}>{headerCell}</TableInput.HeaderCell>
        ))}
      </TableInput.Header>
      <TableInput.Body>
        {productRows.map((row, rowIndex) => (
          <TableInput.Row key={rowIndex}>
            {row.map((cell, cellIndex) => (
              <TableInput.Cell key={cellIndex}>
                <TableInput.CellContent
                  rowTitle={fileStructure.first_column + cellIndex === fileStructure.unique_identifier_column}
                >
                  {cell}
                </TableInput.CellContent>
              </TableInput.Cell>
            ))}
          </TableInput.Row>
        ))}
      </TableInput.Body>
    </TableInput>
  );
};

export {FileTemplatePreview};
