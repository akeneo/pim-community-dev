import React from 'react';
import {TableInput} from './TableInput';
import {fireEvent, render, screen} from '../../../storybook/test-util';

test('it renders its children properly', () => {
  render(
    <TableInput>
      <tbody>
        <tr>
          <td>Table content</td>
        </tr>
      </tbody>
    </TableInput>
  );

  expect(screen.getByText('Table content')).toBeInTheDocument();
});

test('it scrolls', () => {
  render(
    <TableInput data-testid="tableContainer">
      <tbody>
        <tr>
          <td>Table content</td>
        </tr>
      </tbody>
    </TableInput>
  );

  fireEvent.scroll(screen.getByTestId('tableContainer'), {target: {scrollLeft: 100}});
  const tableContainer: HTMLElement = screen.getByTestId('tableContainer');
  expect(tableContainer.children[0]).toHaveClass('shadowed');
});

test('it can drag and drop', () => {
  const handleReorder = jest.fn();
  render(
    <TableInput isDragAndDroppable={true} onReorder={handleReorder}>
      <TableInput.Header>
        <TableInput.HeaderCell>An header</TableInput.HeaderCell>
        <TableInput.HeaderCell>Another header</TableInput.HeaderCell>
      </TableInput.Header>
      <TableInput.Body>
        <TableInput.Row>
          <TableInput.Cell>A cell</TableInput.Cell>
          <TableInput.Cell>Another cell</TableInput.Cell>
        </TableInput.Row>
        <TableInput.Row>
          <TableInput.Cell>A cell</TableInput.Cell>
          <TableInput.Cell>Another cell</TableInput.Cell>
        </TableInput.Row>
        <TableInput.Row>
          <TableInput.Cell>A cell</TableInput.Cell>
          <TableInput.Cell>Another cell</TableInput.Cell>
        </TableInput.Row>
      </TableInput.Body>
    </TableInput>
  );

  fireEvent.mouseDown(screen.getAllByTestId('dragAndDrop')[1]);
  fireEvent.dragStart(screen.getAllByRole('row')[1]);
  fireEvent.dragEnter(screen.getAllByRole('row')[2]);
  fireEvent.dragLeave(screen.getAllByRole('row')[2]);
  fireEvent.dragEnter(screen.getAllByRole('row')[3]);
  fireEvent.drop(screen.getAllByRole('row')[3]);
  fireEvent.dragEnd(screen.getAllByRole('row')[1]);

  expect(handleReorder).toHaveBeenCalledWith([1, 2, 0]);
});

test('TableInput supports ...rest props', () => {
  render(<TableInput data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
