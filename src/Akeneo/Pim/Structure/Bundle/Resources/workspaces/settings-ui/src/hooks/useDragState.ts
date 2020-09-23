import {DragItem, DragState, DragStateContext} from "../components/shared/providers";
import {useCallback, useContext, useState} from "react";

const useDragState = (): DragState => {
    const context = useContext(DragStateContext);

    if (!context) {
        throw new Error("[Context]: You are trying to use 'useContext' outside Provider");
    }

    return context;
};

const getClosestDraggableParent = (element: Element): Element | null => {
    if (element.parentElement === null) {
        return null;
    }

    return element.parentElement.closest('[draggable="true"]');
};

const hasDraggableParent = (element: Element): boolean => {
    const closest = getClosestDraggableParent(element);

    return closest !== null;
}

const useInitialDragState = (isDragEnabled: boolean): DragState => {
    const [dragItem, setDragItem] = useState<DragItem|null>(null);
    const refreshIndex = useCallback((index: number) => {
        if (dragItem === null) {
            return;
        }

        setDragItem({
            data: dragItem.data,
            index
        })
    }, [dragItem,  setDragItem])
    return {
        isDragEnabled,
        dragItem,
        initDragItem: (index: number, data: any) => {
            setDragItem({
                index,
                data
            });
        },
        resetDragItem: () => {
            setDragItem(null);
        },
        handleDragStart: (event, index, data, dragImage) => {
            event.stopPropagation();
            event.persist();

            if (dragImage) {
                event.dataTransfer.setDragImage(dragImage, 0, 0);
            }

            setDragItem({
                index,
                data
            });
        },
        handleDragStartCapture: (event) => {
            console.log('handleDragStartCapture')

            const dropTarget = event.target as Element;

            if (hasDraggableParent(dropTarget)) {
                //dropTarget.style.backgroundColor = 'pink';
                //event.stopPropagation();
                //event.preventDefault();
            }
        },
        handleDragEndCapture: (event) => {
            console.log('handleDragEndCapture')

            const dropTarget = event.target as Element;
            const currentDropTarget = event.currentTarget as Element;

            const closest = getClosestDraggableParent(dropTarget);
            if (closest) {
                event.stopPropagation();
                event.preventDefault();
            }
        },
        handleDragEnterCapture: (event) => {
            console.log('handleDragEnterCapture')

            const dropTarget = event.target as Element;
            const currentDropTarget = event.currentTarget as Element;

            if (dropTarget !== currentDropTarget && hasDraggableParent(currentDropTarget)) {
                //currentDropTarget.style.backgroundColor = 'purple';
                //dropTarget.style.backgroundColor = 'yellow';
                //event.stopPropagation();
                event.preventDefault();
            }
        },
        handleDragLeaveCapture: (event) => {
            console.log('handleDragLeaveCapture');

            const dropTarget = event.target as Element;
            const currentDropTarget = event.currentTarget as Element;

            if (dropTarget !== currentDropTarget && hasDraggableParent(dropTarget)) {
                //currentDropTarget.style.backgroundColor = 'cyan';
                //dropTarget.style.backgroundColor = 'blue';
                //event.stopPropagation();
                event.preventDefault();
            }
        },
        handleDragOver: (event, index, data, dragDownCallback, dragUpCallback) => {
            event.stopPropagation();
            event.preventDefault();
            event.persist();

            const dropTarget = event.target as Element;

            if (dragItem === null || dragItem.data === data) {
                return;
            }

            const hoverBoundingRect = dropTarget.getBoundingClientRect();
            const hoverMiddleY = (hoverBoundingRect.bottom - hoverBoundingRect.top) / 2;
            const hoverClientY = event.clientY - hoverBoundingRect.top;

            if (dragItem.index < index && hoverClientY >= hoverMiddleY) {
                dragDownCallback(dragItem, dropTarget);
                refreshIndex(index);
                return;
            }

            if (dragItem.index > index && hoverClientY <= hoverMiddleY) {
                dragUpCallback(dragItem, dropTarget);
                refreshIndex(index);
                return;
            }

        },
        handleDragEnter: (event, dragEnterCallback) => {
            event.stopPropagation();
            event.preventDefault();
            event.persist();

            if (dragItem === null) {
                return;
            }

            const dropTarget = event.target as Element;

            dragEnterCallback(dragItem, dropTarget);
        },
        handleDragLeave: (event, dragLeaveCallback) => {
            event.stopPropagation();
            event.preventDefault();
            event.persist();

            if (dragItem === null) {
                return;
            }

            const dropTarget = event.target as Element;

            dragLeaveCallback(dragItem, dropTarget);
        },
        handleDrop: (event, dropCallback) => {
            event.stopPropagation();
            event.preventDefault();
            event.persist();

            if (dragItem === null) {
                return;
            }

            const dropTarget = event.target as Element;

            dropCallback(dragItem, dropTarget);

            setDragItem(null);
        },
        handleDragEnd: (event, dragEndCallback) => {
            event.stopPropagation();
            event.preventDefault();
            event.persist();

            if (dragItem === null) {
                return;
            }

            const draggedTarget = event.target as Element;

            dragEndCallback(dragItem, draggedTarget);

            setDragItem(null);
        },
    };
}

export {useDragState, useInitialDragState};