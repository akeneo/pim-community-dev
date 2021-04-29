import React from 'react';
import {screen, fireEvent} from '@testing-library/react';
import {renderWithProviders} from '../../utils';
import {ColumnList} from './ColumnList';
import userEvent from '@testing-library/user-event';

test('it renders a placeholder when no column is selected', () => {
  const columnsConfiguration = [
    {
      uuid: 1,
      target: 'my first column',
      sources: []
    },
    {
      uuid: 2,
      target: 'my second column',
      sources: []
    },
  ];

  renderWithProviders(
    <ColumnList
      columnsConfiguration={columnsConfiguration}
      onColumnChange={jest.fn}
      onColumnCreated={jest.fn}
      onColumnRemoved={jest.fn}
      onColumnSelected={jest.fn}
      selectedColumn={null}
    />
  );

  expect(screen.getByDisplayValue(/my first column/i)).toBeInTheDocument();
  expect(screen.getByDisplayValue(/my second column/i)).toBeInTheDocument();

  expect(screen.getAllByText(/Sources list/i)).toHaveLength(2);

  const firstInput = screen.getAllByPlaceholderText('The column name')[2];
  expect(firstInput).toHaveFocus();
});

test('it remove a column', () => {
  const columnsConfiguration = [
    {
      uuid: 1,
      target: 'my column',
      sources: []
    }
  ];

  const handleRemove = jest.fn();

  renderWithProviders(
    <ColumnList
      columnsConfiguration={columnsConfiguration}
      onColumnChange={jest.fn}
      onColumnCreated={jest.fn}
      onColumnRemoved={handleRemove}
      onColumnSelected={jest.fn}
      selectedColumn={null}
    />
  );

  const removeButton = screen.getByTitle('Remove column')
  fireEvent.click(removeButton);

  expect(handleRemove).toBeCalled();
});

test('it create a new column', () => {
  const columnsConfiguration = [
    {
      uuid: 1,
      target: 'my column',
      sources: []
    }
  ];

  const handleCreate = jest.fn();

  renderWithProviders(
    <ColumnList
      columnsConfiguration={columnsConfiguration}
      onColumnChange={jest.fn}
      onColumnCreated={handleCreate}
      onColumnRemoved={jest.fn}
      onColumnSelected={jest.fn}
      selectedColumn={null}
    />
  );

  const lastInput = screen.getAllByPlaceholderText('The column name')[1];
  userEvent.type(lastInput, 'm');

  expect(handleCreate).toBeCalledWith('m');
});

test('it update a column', () => {
  const columnsConfiguration = [
    {
      uuid: 1,
      target: 'my column',
      sources: []
    }
  ];

  const handleColumnChange = jest.fn();

  renderWithProviders(
    <ColumnList
      columnsConfiguration={columnsConfiguration}
      onColumnChange={handleColumnChange}
      onColumnCreated={jest.fn}
      onColumnRemoved={jest.fn}
      onColumnSelected={jest.fn}
      selectedColumn={null}
    />
  );

  const firstInput = screen.getAllByPlaceholderText('The column name')[0];
  fireEvent.change(firstInput, {target: {value: 'my new column name'}});

  expect(handleColumnChange).toBeCalledWith({
    uuid: 1,
    target: 'my new column name',
    sources: []
  });
});

test('it move to next line when user type enter', async () => {
  const columnsConfiguration = [
    {
      uuid: 1,
      target: 'my column',
      sources: []
    },
    {
      uuid: 2,
      target: 'another column',
      sources: []
    }
  ];

  const handleColumnSelected = jest.fn();

  renderWithProviders(
    <ColumnList
      columnsConfiguration={columnsConfiguration}
      onColumnChange={jest.fn}
      onColumnCreated={jest.fn}
      onColumnRemoved={jest.fn}
      onColumnSelected={handleColumnSelected}
      selectedColumn={null}
    />
  );

  const firstInput = screen.getAllByPlaceholderText('The column name')[0];
  userEvent.type(firstInput, '{enter}');

  expect(handleColumnSelected).toHaveBeenCalledWith(2);
});

test('it focus the selected column', async () => {
  const columnsConfiguration = [
    {
      uuid: 1,
      target: 'my column',
      sources: []
    },
    {
      uuid: 2,
      target: 'another column',
      sources: []
    }
  ];

  renderWithProviders(
    <ColumnList
      columnsConfiguration={columnsConfiguration}
      onColumnChange={jest.fn}
      onColumnCreated={jest.fn}
      onColumnRemoved={jest.fn}
      onColumnSelected={jest.fn}
      selectedColumn={columnsConfiguration[0]}
    />
  );

  const firstInput = screen.getAllByPlaceholderText('The column name')[0];

  expect(firstInput).toHaveFocus();
});
