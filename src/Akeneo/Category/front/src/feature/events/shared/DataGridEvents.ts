import React from 'react';

const handleDragStart = (event: React.DragEvent, dragImage: Element | null) => {
  event.stopPropagation();
  event.persist();

  if (dragImage) {
    event.dataTransfer.setDragImage(dragImage, 0, 0);
  }
};

const handleDragOver = <T extends {}>(
  event: React.DragEvent,
  draggedIndex: number,
  draggedData: T | null,
  activeDropZoneIndex: number,
  activeDropZoneData: T | null,
  dragDownCallback: Function,
  dragUpCallback: Function,
  sameData: (source: T, target: T) => boolean
) => {
  event.stopPropagation();
  event.preventDefault();
  event.persist();

  const dropTarget = event.target as Element;
  if (draggedData === null || activeDropZoneData === null || sameData(draggedData, activeDropZoneData)) {
    return;
  }

  const hoverBoundingRect = dropTarget.getBoundingClientRect();
  const hoverMiddleY = (hoverBoundingRect.bottom - hoverBoundingRect.top) / 2;
  const hoverClientY = event.clientY - hoverBoundingRect.top;

  if (draggedIndex < activeDropZoneIndex && hoverClientY >= hoverMiddleY) {
    dragDownCallback(draggedData, activeDropZoneData);
    return;
  }

  if (draggedIndex > activeDropZoneIndex && hoverClientY <= hoverMiddleY) {
    dragUpCallback(draggedData, activeDropZoneData);
    return;
  }
};

const handleDrop = (event: React.DragEvent, dropCallback: Function) => {
  event.stopPropagation();
  event.preventDefault();
  event.persist();

  dropCallback();
};

const handleDragEnd = (event: React.DragEvent, dragEndCallback: Function) => {
  event.stopPropagation();
  event.preventDefault();
  event.persist();

  dragEndCallback();
};

export {handleDrop, handleDragOver, handleDragEnd, handleDragStart};
