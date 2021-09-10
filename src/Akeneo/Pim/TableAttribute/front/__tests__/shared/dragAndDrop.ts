import {fireEvent, screen} from '@testing-library/react';

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

  fireEvent.mouseDown(screen.getAllByTestId('dragAndDrop')[fromIndex + 1]);
  fireEvent.dragStart(screen.getAllByRole('row')[fromIndex + 1], {dataTransfer});

  for (let i = fromIndex + 2; i < afterIndex; i++) {
    fireEvent.dragEnter(screen.getAllByRole('row')[i], {dataTransfer});
    fireEvent.dragLeave(screen.getAllByRole('row')[i], {dataTransfer});
  }

  fireEvent.dragEnter(screen.getAllByRole('row')[afterIndex], {dataTransfer});
  fireEvent.drop(screen.getAllByRole('row')[afterIndex], {dataTransfer});
  fireEvent.dragEnd(screen.getAllByRole('row')[fromIndex + 1], {dataTransfer});
};

export {dragAndDrop};
