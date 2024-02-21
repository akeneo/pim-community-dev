import React, {memo, useCallback} from 'react';
import {useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {AttributeOption} from '../model';
import {AkeneoThemedProps, CloseIcon, getColor, IconButton, RowIcon, Table} from 'akeneo-design-system';
import styled from 'styled-components';
import AttributeOptionQualityBadge from './AttributeOptionQualityBadge';

type Props = {
  isDraggable: boolean;
  attributeOption: AttributeOption;
  onSelectItem: (optionId: number) => void;
  isSelected: boolean;
  onDelete: (attributeOption: AttributeOption) => void;
};

const AttributeOptionRow = memo(
  ({isDraggable, attributeOption, onSelectItem, isSelected, onDelete, ...rest}: Props) => {
    const locale = useUserContext().get('catalogLocale');
    const translate = useTranslate();

    const handleDelete = useCallback(() => {
      onDelete(attributeOption);
    }, [onDelete, attributeOption]);

    const handleSelectRow = useCallback(() => onSelectItem(attributeOption.id), [onSelectItem, attributeOption]);

    return (
      <TableRow
        data-testid="attribute-option-item"
        data-attribute-option-role="item"
        isDraggable={isDraggable}
        isSelected={isSelected}
        onClick={handleSelectRow}
        data-is-selected={isSelected}
        {...rest}
      >
        {!isDraggable && (
          <TableCellNoDraggable>
            <HandleContainer>
              <RowIcon size={16} />
            </HandleContainer>
          </TableCellNoDraggable>
        )}
        <TableCellLabel data-testid="attribute-option-item-label" rowTitle={true}>
          {attributeOption.optionValues[locale] && attributeOption.optionValues[locale].value
            ? attributeOption.optionValues[locale].value
            : `[${attributeOption.code}]`}
        </TableCellLabel>
        <Table.Cell data-testid="attribute-option-item-code" data-attribute-option-role="item-code">
          {attributeOption.code}
        </Table.Cell>
        <Table.Cell>
          <AttributeOptionQualityBadge toImprove={attributeOption.toImprove} />
        </Table.Cell>
        <Table.ActionCell>
          <IconButton
            icon={<CloseIcon />}
            onClick={handleDelete}
            title={translate('pim_common.delete')}
            ghost="borderless"
            level="tertiary"
            data-testid="attribute-option-delete-button"
          />
        </Table.ActionCell>
      </TableRow>
    );
  }
);

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

const TableRow = styled(Table.Row)<{isDraggable: boolean} & AkeneoThemedProps>`
  td:first-child {
    color: ${({isDraggable}) => (isDraggable ? getColor('grey', 100) : getColor('grey', 40))};
  }
`;

export {AttributeOptionRow};
