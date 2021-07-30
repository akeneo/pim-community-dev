import React, {FC, useRef, useState} from 'react';
import {AttributeOption} from '../model';
import DeleteConfirmationModal from './DeleteConfirmationModal';
import {useAttributeContext} from '../contexts';
import {CloseIcon, IconButton, Table} from 'akeneo-design-system';
import {useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import styled from 'styled-components';

export type DragItem = {
  code: string;
  index: number;
};

interface AttributeOptionItemProps {
  data: AttributeOption;
  selectAttributeOption: (selectedOptionId: number) => void;
  isSelected: boolean;
  deleteAttributeOption: (optionId: number) => void;
  isDraggable: boolean;
  moveAttributeOption: (sourceOptionCode: string, targetOptionCode: string) => void;
  validateMoveAttributeOption: () => void;
  dragItem: DragItem | null;
  setDragItem: (dragitem: DragItem | null) => void;
  index: number;
}

const AttributeOptionTableRow: FC<AttributeOptionItemProps> = ({children, ...props}) => {
  const {
    data,
    selectAttributeOption,
    isSelected,
    deleteAttributeOption,
    isDraggable,
    moveAttributeOption,
    validateMoveAttributeOption,
    dragItem,
    setDragItem,
    index,
  } = props;
  const [showDeleteConfirmationModal, setShowDeleteConfirmationModal] = useState<boolean>(false);
  const attributeContext = useAttributeContext();
  const rowRef = useRef(null);
  const locale = useUserContext().get('catalogLocale');
  const translate = useTranslate();

  const deleteOption = () => {
    setShowDeleteConfirmationModal(false);
    deleteAttributeOption(data.id);
  };

  const onDragStart = (event: any) => {
    event.stopPropagation();
    event.persist();
    event.dataTransfer.setDragImage(rowRef.current, 0, 0);
    setDragItem({code: data.code, index});
  };

  const onDragStartCapture = (event: any) => {
    if (attributeContext.autoSortOptions || !event.target.classList.contains('AknAttributeOption-move-icon')) {
      event.preventDefault();
      event.stopPropagation();
    }
  };

  const onDragEndCapture = (event: any) => {
    if (attributeContext.autoSortOptions || !event.target.classList.contains('AknAttributeOption-move-icon')) {
      event.preventDefault();
      event.stopPropagation();
    }
  };

  const onDragOver = (event: any) => {
    event.stopPropagation();
    event.preventDefault();
    event.persist();

    if (dragItem === null || dragItem.code === data.code) {
      return;
    }

    const hoverBoundingRect = event.target.getBoundingClientRect();
    const hoverMiddleY = (hoverBoundingRect.bottom - hoverBoundingRect.top) / 2;
    const hoverClientY = (event.clientY || event.target.clientY) - hoverBoundingRect.top;

    // Dragging downwards
    if (dragItem.index < index && hoverClientY < hoverMiddleY) {
      return;
    }
    // Dragging upwards
    if (dragItem.index > index && hoverClientY > hoverMiddleY) {
      return;
    }

    moveAttributeOption(dragItem.code, data.code);
  };

  const onDrop = (event: any) => {
    event.stopPropagation();
    event.preventDefault();
    event.persist();
    if (dragItem !== null) {
      validateMoveAttributeOption();
      setDragItem(null);
    }
  };

  return (
    <>
      <Table.Row
        isSelected={isSelected}
        draggable={isDraggable}
        onClick={() => selectAttributeOption(data.id)}
        key={`${data.code}${index}`}
      >
        <Table.Cell rowTitle={true}>{data.code}</Table.Cell>
        <Table.Cell>{children}</Table.Cell>
        <TableActionCell>
          <IconButton
            icon={<CloseIcon />}
            onClick={(event: any) => {
              event.preventDefault();
              event.stopPropagation();
              setShowDeleteConfirmationModal(true);
            }}
            title={translate('pim_common.delete')}
            ghost="borderless"
            level="tertiary"
          />
        </TableActionCell>
      </Table.Row>

      {showDeleteConfirmationModal && (
        <DeleteConfirmationModal
          attributeOptionCode={data.code}
          confirmDelete={deleteOption}
          cancelDelete={() => setShowDeleteConfirmationModal(false)}
        />
      )}
    </>
  );
};

const TableActionCell = styled(Table.ActionCell)`
  width: 50px;
`;

export default AttributeOptionTableRow;
