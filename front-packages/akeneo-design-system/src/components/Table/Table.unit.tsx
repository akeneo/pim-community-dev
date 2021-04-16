import React from 'react';
import {Table} from './Table';
import {render, screen} from '../../storybook/test-util';

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

test('Table supports ...rest props', () => {
  render(<Table data-testid="my_value" />);

  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
