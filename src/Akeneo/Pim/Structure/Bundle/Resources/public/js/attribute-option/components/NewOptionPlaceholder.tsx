import React, {useRef} from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useScrollIntoView} from '../hooks/useScrollIntoView';
import {CloseIcon, getColor, IconButton, RowIcon, Table} from 'akeneo-design-system';
import styled from 'styled-components';

interface newOptionPlaceholderProps {
  cancelNewOption: () => void;
  isDraggable: boolean;
}

const NewOptionPlaceholder = ({cancelNewOption, isDraggable}: newOptionPlaceholderProps) => {
  const translate = useTranslate();
  const placeholderRef = useRef<HTMLTableRowElement>(null);

  useScrollIntoView(placeholderRef);

  return (
    <TableRow isSelected={true} ref={placeholderRef} data-testid="new-option-placeholder">
      {!isDraggable && (
        <TableCellNoDraggable>
          <HandleContainer>
            <RowIcon size={16} />
          </HandleContainer>
        </TableCellNoDraggable>
      )}
      <TableCellLabel rowTitle={true}>&nbsp;</TableCellLabel>
      <Table.Cell>{translate('pim_enrich.entity.attribute_option.module.edit.new_option_code')}</Table.Cell>
      <Table.Cell>&nbsp;</Table.Cell>
      <Table.ActionCell>
        <IconButton
          icon={<CloseIcon />}
          onClick={() => cancelNewOption()}
          title={translate('pim_common.delete')}
          ghost="borderless"
          level="tertiary"
          data-testid="new-option-cancel"
        />
      </Table.ActionCell>
    </TableRow>
  );
};

const TableRow = styled(Table.Row)`
  td:first-child {
    color: ${getColor('grey', 40)};
  }
`;

const TableCellLabel = styled(Table.Cell)`
  width: 35%;
`;

const TableCellNoDraggable = styled(Table.Cell)`
  width: 40px;
`;

const HandleContainer = styled.div`
  display: flex;
  align-items: center;
  justify-content: center;
`;

export default NewOptionPlaceholder;
