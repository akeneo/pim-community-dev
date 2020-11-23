import React from 'react';
import {Table} from '../Table';
import {fireEvent, render, screen} from '../../../storybook/test-util';

test('it renders its children properly', () => {
  render(
    <Table>
      <Table.Body>
        <Table.Row>
          <Table.Cell>An value</Table.Cell>
          <Table.Cell>Another value</Table.Cell>
        </Table.Row>
      </Table.Body>
    </Table>
  );

  expect(screen.getByText('An value')).toBeInTheDocument();
  expect(screen.getByText('Another value')).toBeInTheDocument();
});

test('it calls onSelectToggle handler when user clicks on selectable row', () => {
  const onSelectToggle = jest.fn();
  render(
    <Table isSelectable={true}>
      <Table.Body>
        <Table.Row onSelectToggle={onSelectToggle} isSelected={true}>
          <Table.Cell>An value</Table.Cell>
          <Table.Cell>Another value</Table.Cell>
        </Table.Row>
      </Table.Body>
    </Table>
  );

  const checkbox = screen.getByRole('checkbox', {hidden: true});
  fireEvent.click(checkbox);

  expect(onSelectToggle).toBeCalled();
});

test('it calls onClick handler when user clicks on row', () => {
  const onClick = jest.fn();
  render(
    <Table>
      <Table.Body>
        <Table.Row onClick={onClick}>
          <Table.Cell>An value</Table.Cell>
          <Table.Cell>Another value</Table.Cell>
        </Table.Row>
      </Table.Body>
    </Table>
  );

  const row = screen.getByRole('row');
  fireEvent.click(row);

  expect(onClick).toBeCalled();
});

test('it throws when onSelectToggle is not given on selectable table', () => {
  const mockConsole = jest.spyOn(console, 'error').mockImplementation();
  const cellRender = () =>
    render(
      <Table isSelectable={true}>
        <Table.Body>
          <Table.Row isSelected={true}>
            <Table.Cell>An value</Table.Cell>
            <Table.Cell>Another value</Table.Cell>
          </Table.Row>
        </Table.Body>
      </Table>
    );

  expect(cellRender).toThrowError();

  mockConsole.mockRestore();
});

test('it throws when isSelected is not given on selectable table', () => {
  const mockConsole = jest.spyOn(console, 'error').mockImplementation();

  const onSelectToggle = jest.fn();
  const cellRender = () =>
    render(
      <Table isSelectable={true}>
        <Table.Body>
          <Table.Row onSelectToggle={onSelectToggle}>
            <Table.Cell>An value</Table.Cell>
            <Table.Cell>Another value</Table.Cell>
          </Table.Row>
        </Table.Body>
      </Table>
    );

  expect(cellRender).toThrowError();

  mockConsole.mockRestore();
});

// Those tests should pass directly if you follow the contributing guide.
// If you add required props to your Component, these tests will fail
// and you will need to add these required props here as well
test('Table.Row supports forwardRef', () => {
  const ref = {current: null};
  render(
    <Table>
      <Table.Body>
        <Table.Row ref={ref}>
          <Table.Cell>An value</Table.Cell>
        </Table.Row>
      </Table.Body>
    </Table>
  );

  expect(ref.current).not.toBe(null);
});

test('Table.Row supports ...rest props', () => {
  render(
    <Table>
      <Table.Body>
        <Table.Row data-testid="my_value" />
      </Table.Body>
    </Table>
  );

  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
