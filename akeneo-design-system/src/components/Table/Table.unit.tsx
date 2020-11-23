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
  render(
    <Table isSelectable={true}>
      <Table.Header>
        <Table.HeaderCell>An header</Table.HeaderCell>
        <Table.HeaderCell>Another header</Table.HeaderCell>
      </Table.Header>
      <Table.Body>
        <Table.Row isSelected={false} onSelectToggle={() => {}}>
          <Table.Cell>A cell</Table.Cell>
          <Table.Cell>Another cell</Table.Cell>
        </Table.Row>
      </Table.Body>
    </Table>
  );

  expect(screen.queryByRole('checkbox')).not.toBeInTheDocument();
});

test('it renders table with checkbox when it is selectable and display checkbox', () => {
  render(
    <Table isSelectable={true} displayCheckbox={true}>
      <Table.Header>
        <Table.HeaderCell>An header</Table.HeaderCell>
        <Table.HeaderCell>Another header</Table.HeaderCell>
      </Table.Header>
      <Table.Body>
        <Table.Row isSelected={false} onSelectToggle={() => {}}>
          <Table.Cell>A cell</Table.Cell>
          <Table.Cell>Another cell</Table.Cell>
        </Table.Row>
      </Table.Body>
    </Table>
  );

  expect(screen.queryByRole('checkbox')).toBeInTheDocument();
});

test('it renders table with checkbox when it is selectable and row is selected', () => {
  render(
    <Table isSelectable={true}>
      <Table.Header>
        <Table.HeaderCell>An header</Table.HeaderCell>
        <Table.HeaderCell>Another header</Table.HeaderCell>
      </Table.Header>
      <Table.Body>
        <Table.Row isSelected={true} onSelectToggle={() => {}}>
          <Table.Cell>A cell</Table.Cell>
          <Table.Cell>Another cell</Table.Cell>
        </Table.Row>
      </Table.Body>
    </Table>
  );

  expect(screen.queryByRole('checkbox')).toBeInTheDocument();
});

// Those tests should pass directly if you follow the contributing guide.
// If you add required props to your Component, these tests will fail
// and you will need to add these required props here as well
test('Table supports ...rest props', () => {
  render(<Table data-testid="my_value" />);

  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
