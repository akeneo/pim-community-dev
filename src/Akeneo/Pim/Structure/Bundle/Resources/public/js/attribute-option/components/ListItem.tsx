import React, {FC, useCallback, useMemo, useRef, useState} from 'react';
import {AttributeOption} from '../model';
import DeleteConfirmationModal from './DeleteConfirmationModal';
import {useAttributeContext, useAttributeOptionsContext} from '../hooks';

export type DragItem = {
    code: string;
    index: number;
};

interface AttributeOptionItemProps {
    data: AttributeOption;
    selectAttributeOption: (selectedOptionId: number) => void;
    isSelected: boolean;
    moveAttributeOption: (sourceOptionCode: string, targetOptionCode: string) => void;
    validateMoveAttributeOption: () => void;
    dragItem: DragItem | null;
    setDragItem: (dragitem: DragItem | null) => void;
    index: number;
}

const ListItem: FC<AttributeOptionItemProps> = ({children, ...props}) => {
    const {data, selectAttributeOption, isSelected, moveAttributeOption, validateMoveAttributeOption, dragItem, setDragItem, index} = props;
    const [showDeleteConfirmationModal, setShowDeleteConfirmationModal] = useState<boolean>(false);
    const {remove} = useAttributeOptionsContext();
    const attributeContext = useAttributeContext();
    const rowRef = useRef(null);

    const deleteOption = useCallback(async () => {
        setShowDeleteConfirmationModal(false);
        await remove(data.id);
    }, [setShowDeleteConfirmationModal, remove, data]);

    const cancelDeleteOption = useCallback(() => {
        setShowDeleteConfirmationModal(false);
    }, [setShowDeleteConfirmationModal])

    const onDragStart = useCallback((event: any) => {
        event.stopPropagation();
        event.persist();
        event.dataTransfer.setDragImage(rowRef.current, 0, 0);
        setDragItem({code: data.code, index});
    }, [setDragItem]);

    const onDragStartCapture = useCallback((event: any) => {
        if (attributeContext.autoSortOptions || !event.target.classList.contains('AknAttributeOption-move-icon')) {
            event.preventDefault();
            event.stopPropagation();
        }
    }, [attributeContext.autoSortOptions]);

    const onDragEndCapture = useCallback((event: any) => {
        if (attributeContext.autoSortOptions || !event.target.classList.contains('AknAttributeOption-move-icon')) {
            event.preventDefault();
            event.stopPropagation();
        }
    }, [attributeContext.autoSortOptions]);

    const onDragOver = useCallback((event: any) => {
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
    }, [dragItem, data, moveAttributeOption, index]);

    const onDrop = useCallback((event: any) => {
        event.stopPropagation();
        event.preventDefault();
        event.persist();

        if (dragItem !== null) {
            validateMoveAttributeOption();
            setDragItem(null);
        }
    }, [dragItem, setDragItem, validateMoveAttributeOption]);

    const onDelete = useCallback((event: any) => {
        event.preventDefault();
        event.stopPropagation();
        setShowDeleteConfirmationModal(true);
    }, [setShowDeleteConfirmationModal]);

    const onSelect = useCallback(() => {
        selectAttributeOption(data.id);
    }, [data.id, selectAttributeOption]);

    const className = useMemo<string>(() => {
       return `AknAttributeOption-listItem ${isSelected || (dragItem !== null && dragItem.code === data.code) ? 'AknAttributeOption-listItem--selected' : ''}`;
    }, [isSelected, dragItem, data.code]);

    return (
        <>
            <div
                className={className}
                role="attribute-option-item"
                onClick={onSelect}
                draggable={true}
                onDragStartCapture={onDragStartCapture}
                onDragEndCapture={onDragEndCapture}
                onDragStart={onDragStart}
                onDragOver={onDragOver}
                onDrop={onDrop}
                onDragEnd={onDrop}
                style={dragItem !== null && dragItem.code === data.code ? {opacity: 0.4} : {}}
                ref={rowRef}
            >
                <span className={`AknAttributeOption-move-icon ${attributeContext.autoSortOptions ? 'AknAttributeOption-move-icon--disabled' : ''}`} draggable={true} role={'attribute-option-move-handle'}/>
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
                    onClick={onDelete}
                    role="attribute-option-delete-button"
                />
            </div>

            {showDeleteConfirmationModal && (
                <DeleteConfirmationModal
                    attributeOptionCode={data.code}
                    confirmDelete={deleteOption}
                    cancelDelete={cancelDeleteOption}
                />)
            }
        </>
    );
};

export default ListItem;
