import {fireEvent, screen, createEvent} from '@testing-library/react';

const dragAndDrop = (fromIndex: number, afterIndex: number) => {
  let dataTransferred = '';
  const dataTransfer = {
    getData: (_format: string) => {
      return dataTransferred;
    },
    setData: (_format: string, data: string) => {
      dataTransferred = data;
    },
  };
  const ev = {dataTransfer};

  fireEvent.mouseDown(screen.getAllByTestId('dragAndDrop')[fromIndex + 1]);
  const sourceElement = screen.getAllByRole('row')[fromIndex + 1];
  const mockDropEvent = createEvent.dragStart(sourceElement);
  Object.assign(mockDropEvent, ev);
  fireEvent(sourceElement, mockDropEvent);

  for (let i = fromIndex + 2; i < afterIndex; i++) {
    const targetElement = screen.getAllByRole('row')[i];
    const mockEnterEvent = createEvent.dragEnter(targetElement);
    Object.assign(mockEnterEvent, ev);
    fireEvent(targetElement, mockEnterEvent);
    fireEvent.dragLeave(screen.getAllByRole('row')[i], {dataTransfer});
  }

  const targetElement = screen.getAllByRole('row')[afterIndex];
  const mockEnterEvent = createEvent.dragEnter(targetElement);
  Object.assign(mockEnterEvent, ev);
  fireEvent(targetElement, mockEnterEvent);
  fireEvent.drop(screen.getAllByRole('row')[afterIndex], {dataTransfer});
  fireEvent.dragEnd(screen.getAllByRole('row')[fromIndex + 1], {dataTransfer});
};

export {dragAndDrop};
