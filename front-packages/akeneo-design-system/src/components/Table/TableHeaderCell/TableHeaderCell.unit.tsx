import React from 'react';
import {Table} from '../Table';
import {fireEvent, render, screen} from '../../../storybook/test-util';

test('it renders its children properly', () => {
  render(
    <Table>
      <Table.Header>
        <Table.HeaderCell>An header</Table.HeaderCell>
        <Table.HeaderCell>Another header</Table.HeaderCell>
      </Table.Header>
    </Table>
  );

  expect(screen.getByText('An header')).toBeInTheDocument();
  expect(screen.getByText('Another header')).toBeInTheDocument();
});

test('it calls onDirectionChange handler when user clicks on sortable cell', () => {
  const onDirectionChange = jest.fn();
  render(
    <Table>
      <Table.Header>
        <Table.HeaderCell isSortable={true} onDirectionChange={onDirectionChange} sortDirection="none">
          An header
        </Table.HeaderCell>
        <Table.HeaderCell>Another header</Table.HeaderCell>
      </Table.Header>
    </Table>
  );

  const header = screen.getByText('An header');
  fireEvent.click(header);

  expect(onDirectionChange).toBeCalledWith('ascending');
});

test('it calls onDirectionChange handler when user clicks on sortable descending cell', () => {
  const onDirectionChange = jest.fn();
  render(
    <Table>
      <Table.Header>
        <Table.HeaderCell isSortable={true} onDirectionChange={onDirectionChange} sortDirection="descending">
          An header
        </Table.HeaderCell>
        <Table.HeaderCell>Another header</Table.HeaderCell>
      </Table.Header>
    </Table>
  );

  const header = screen.getByText('An header');
  fireEvent.click(header);

  expect(onDirectionChange).toBeCalledWith('ascending');
});

test('it calls onDirectionChange handler when user clicks on sortable ascending cell', () => {
  const onDirectionChange = jest.fn();
  render(
    <Table>
      <Table.Header>
        <Table.HeaderCell isSortable={true} onDirectionChange={onDirectionChange} sortDirection="ascending">
          An header
        </Table.HeaderCell>
        <Table.HeaderCell>Another header</Table.HeaderCell>
      </Table.Header>
    </Table>
  );

  const header = screen.getByText('An header');
  fireEvent.click(header);

  expect(onDirectionChange).toBeCalledWith('descending');
});

test('it throws when onDirectionChange is given on not sortable row', () => {
  const mockConsole = jest.spyOn(console, 'error').mockImplementation();

  const onDirectionChange = jest.fn();
  const headerCellRender = () =>
    render(
      <Table>
        <Table.Header>
          <Table.HeaderCell isSortable={false} onDirectionChange={onDirectionChange} sortDirection="descending">
            An header
          </Table.HeaderCell>
          <Table.HeaderCell>Another header</Table.HeaderCell>
        </Table.Header>
      </Table>
    );

  expect(headerCellRender).toThrowError();

  mockConsole.mockRestore();
});

test('it throws when onDirectionChange is not given on sortable row', () => {
  const mockConsole = jest.spyOn(console, 'error').mockImplementation();
  const headerCellRender = () =>
    render(
      <Table>
        <Table.Header>
          <Table.HeaderCell isSortable={true}>An header</Table.HeaderCell>
          <Table.HeaderCell>Another header</Table.HeaderCell>
        </Table.Header>
      </Table>
    );

  expect(headerCellRender).toThrowError();

  mockConsole.mockRestore();
});

test('Table.HeaderCell supports forwardRef', () => {
  const ref = {current: null};
  render(
    <Table>
      <Table.Header>
        <Table.HeaderCell ref={ref}>An header</Table.HeaderCell>
      </Table.Header>
    </Table>
  );

  expect(ref.current).not.toBe(null);
});

test('Table.HeaderCell supports ...rest props', () => {
  render(
    <Table>
      <Table.Header>
        <Table.HeaderCell data-testid="my_value" />
      </Table.Header>
    </Table>
  );

  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
