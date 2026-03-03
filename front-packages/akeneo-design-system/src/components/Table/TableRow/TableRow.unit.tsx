import React from 'react';
import {Table} from '../Table';
import {fireEvent, render, screen} from '../../../storybook/test-util';

test('it renders its children properly', () => {
  render(
    <Table>
      <Table.Body>
        <Table.Row>
          <Table.Cell>A value</Table.Cell>
          <Table.Cell>Another value</Table.Cell>
        </Table.Row>
      </Table.Body>
    </Table>
  );

  expect(screen.getByText('A value')).toBeInTheDocument();
  expect(screen.getByText('Another value')).toBeInTheDocument();
});

test('it calls onSelectToggle handler when user clicks on selectable row', () => {
  const onSelectToggle = jest.fn();
  render(
    <Table isSelectable={true}>
      <Table.Body>
        <Table.Row onSelectToggle={onSelectToggle} isSelected={true}>
          <Table.Cell>A value</Table.Cell>
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
          <Table.Cell>A value</Table.Cell>
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
            <Table.Cell>A value</Table.Cell>
            <Table.Cell>Another value</Table.Cell>
          </Table.Row>
        </Table.Body>
      </Table>
    );

  expect(cellRender).toThrowError();

  mockConsole.mockRestore();
});

test('Table.Row supports forwardRef', () => {
  const ref = {current: null};
  render(
    <Table>
      <Table.Body>
        <Table.Row ref={ref}>
          <Table.Cell>A value</Table.Cell>
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

test('it renders warning icon when row has a warning', () => {
  render(
    <Table hasWarningRows>
      <Table.Body>
        <Table.Row level="warning">
          <Table.Cell>A value</Table.Cell>
          <Table.Cell>Another value</Table.Cell>
        </Table.Row>
      </Table.Body>
    </Table>
  );

  expect(screen.getByTestId('warning-icon')).toBeInTheDocument();
});

test('it renders lock icon when row is locked', () => {
  render(
    <Table hasLockedRows>
      <Table.Body>
        <Table.Row level="tertiary">
          <Table.Cell>A value</Table.Cell>
          <Table.Cell>Another value</Table.Cell>
        </Table.Row>
      </Table.Body>
    </Table>
  );

  expect(screen.getByTestId('lock-icon')).toBeInTheDocument();
});
