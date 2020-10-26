import React, {FC, useRef, useState} from 'react';
import {AttributeOption} from '../model';
import DeleteConfirmationModal from './DeleteConfirmationModal';
import {useAttributeContext} from '../contexts';

export type DragItem = {
  code: string;
  index: number;
};

interface AttributeOptionItemProps {
  data: AttributeOption;
  selectAttributeOption: (selectedOptionId: number) => void;
  isSelected: boolean;
  deleteAttributeOption: (optionId: number) => void;
  moveAttributeOption: (sourceOptionCode: string, targetOptionCode: string) => void;
  validateMoveAttributeOption: () => void;
  dragItem: DragItem | null;
  setDragItem: (dragitem: DragItem | null) => void;
  index: number;
}

const ListItem: FC<AttributeOptionItemProps> = ({children, ...props}) => {
  const {
    data,
    selectAttributeOption,
    isSelected,
    deleteAttributeOption,
    moveAttributeOption,
    validateMoveAttributeOption,
    dragItem,
    setDragItem,
    index,
  } = props;
  const [showDeleteConfirmationModal, setShowDeleteConfirmationModal] = useState<boolean>(false);
  const attributeContext = useAttributeContext();
  const rowRef = useRef(null);

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

  const className = `AknAttributeOption-listItem ${
    isSelected || (dragItem !== null && dragItem.code === data.code) ? 'AknAttributeOption-listItem--selected' : ''
  }`;

  return (
    <>
      <div
        className={className}
        role="attribute-option-item"
        onClick={() => selectAttributeOption(data.id)}
        draggable={true}
        onDragStartCapture={onDragStartCapture}
        onDragEndCapture={onDragEndCapture}
        onDragStart={onDragStart}
        onDragOver={(event: any) => onDragOver(event)}
        onDrop={(event: any) => onDrop(event)}
        onDragEnd={(event: any) => onDrop(event)}
        style={dragItem !== null && dragItem.code === data.code ? {opacity: 0.4} : {}}
        ref={rowRef}
      >
        <span
          className={`AknAttributeOption-move-icon ${
            attributeContext.autoSortOptions ? 'AknAttributeOption-move-icon--disabled' : ''
          }`}
          draggable={true}
          role={'attribute-option-move-handle'}
        />
        <span className="AknAttributeOption-itemCode" role="attribute-option-item-label">
          <div>
            <span>{data.code}</span>
          </div>
        </span>
        <span className="AknAttributeOption-extraData" role="attribute-option-extra-data">
          <span>{children}</span>
        </span>
        <span
          className="AknAttributeOption-delete-option-icon"
          onClick={(event: any) => {
            event.preventDefault();
            event.stopPropagation();
            setShowDeleteConfirmationModal(true);
          }}
          role="attribute-option-delete-button"
        />
      </div>

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

export default ListItem;
