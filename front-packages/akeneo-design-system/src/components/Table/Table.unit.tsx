import React from 'react';
import {Table} from './Table';
import {render, screen} from '../../storybook/test-util';
import {fireEvent} from '@testing-library/dom';

test('it renders its children properly', () => {
  render(
    <Table>
      <Table.Header>
        <Table.HeaderCell>An header</Table.HeaderCell>
        <Table.HeaderCell>Another header</Table.HeaderCell>
      </Table.Header>
      <Table.Body>
        <Table.Row>
          <Table.Cell>A cell</Table.Cell>
          <Table.Cell>Another cell</Table.Cell>
        </Table.Row>
      </Table.Body>
    </Table>
  );

  expect(screen.getByText('An header')).toBeInTheDocument();
  expect(screen.getByText('Another header')).toBeInTheDocument();
  expect(screen.getByText('A cell')).toBeInTheDocument();
  expect(screen.getByText('Another cell')).toBeInTheDocument();
});

test('it renders table without checkbox when it is selectable but not selected', () => {
  const onSelectToggle = jest.fn();

  render(
    <Table isSelectable={true}>
      <Table.Header>
        <Table.HeaderCell>An header</Table.HeaderCell>
        <Table.HeaderCell>Another header</Table.HeaderCell>
      </Table.Header>
      <Table.Body>
        <Table.Row isSelected={false} onSelectToggle={onSelectToggle}>
          <Table.Cell>A cell</Table.Cell>
          <Table.Cell>Another cell</Table.Cell>
        </Table.Row>
      </Table.Body>
    </Table>
  );

  expect(screen.queryByRole('checkbox')).not.toBeInTheDocument();
});

test('it renders table with checkbox when it is selectable and display checkbox', () => {
  const onSelectToggle = jest.fn();

  render(
    <Table isSelectable={true} displayCheckbox={true}>
      <Table.Header>
        <Table.HeaderCell>An header</Table.HeaderCell>
        <Table.HeaderCell>Another header</Table.HeaderCell>
      </Table.Header>
      <Table.Body>
        <Table.Row isSelected={false} onSelectToggle={onSelectToggle}>
          <Table.Cell>A cell</Table.Cell>
          <Table.Cell>Another cell</Table.Cell>
        </Table.Row>
      </Table.Body>
    </Table>
  );

  expect(screen.queryByRole('checkbox')).toBeInTheDocument();
});

test('it renders table with checkbox when it is selectable and row is selected', () => {
  const onSelectToggle = jest.fn();

  render(
    <Table isSelectable={true}>
      <Table.Header>
        <Table.HeaderCell>An header</Table.HeaderCell>
        <Table.HeaderCell>Another header</Table.HeaderCell>
      </Table.Header>
      <Table.Body>
        <Table.Row isSelected={true} onSelectToggle={onSelectToggle}>
          <Table.Cell>A cell</Table.Cell>
          <Table.Cell>Another cell</Table.Cell>
        </Table.Row>
      </Table.Body>
    </Table>
  );

  expect(screen.queryByRole('checkbox')).toBeInTheDocument();
});

test('it renders table with drag and drop', () => {
  const handleReorder = jest.fn();

  render(
    <Table isDragAndDroppable={true} onReorder={handleReorder}>
      <Table.Header>
        <Table.HeaderCell>An header</Table.HeaderCell>
        <Table.HeaderCell>Another header</Table.HeaderCell>
      </Table.Header>
      <Table.Body>
        <Table.Row>
          <Table.Cell>A cell</Table.Cell>
          <Table.Cell>Another cell</Table.Cell>
        </Table.Row>
        <Table.Row>
          <Table.Cell>A cell</Table.Cell>
          <Table.Cell>Another cell</Table.Cell>
        </Table.Row>
        <Table.Row>
          <Table.Cell>A cell</Table.Cell>
          <Table.Cell>Another cell</Table.Cell>
        </Table.Row>
      </Table.Body>
    </Table>
  );

  let dataTransferred = '';
  const dataTransfer = {
    getData: (_format: string) => {
      return dataTransferred;
    },
    setData: (_format: string, data: string) => {
      dataTransferred = data;
    },
  };

  fireEvent.mouseDown(screen.getAllByTestId('dragAndDrop')[1]);
  fireEvent.dragStart(screen.getAllByRole('row')[1], {dataTransfer});
  fireEvent.dragEnter(screen.getAllByRole('row')[2], {dataTransfer});
  fireEvent.dragLeave(screen.getAllByRole('row')[2], {dataTransfer});
  fireEvent.dragEnter(screen.getAllByRole('row')[3], {dataTransfer});
  fireEvent.drop(screen.getAllByRole('row')[3], {dataTransfer});
  fireEvent.dragEnd(screen.getAllByRole('row')[1], {dataTransfer});

  expect(handleReorder).toHaveBeenCalledWith([1, 2, 0]);
});

test('Table supports ...rest props', () => {
  render(<Table data-testid="my_value" />);

  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
