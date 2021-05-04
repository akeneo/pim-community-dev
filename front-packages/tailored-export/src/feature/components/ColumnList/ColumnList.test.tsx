import React from 'react';
import {screen, fireEvent} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {ColumnList} from './ColumnList';
import userEvent from '@testing-library/user-event';
import {ValidationErrorsContext} from '../../contexts';

test('it renders a placeholder when no column is selected', () => {
  const columnsConfiguration = [
    {
      uuid: 1,
      target: 'my first column',
      sources: [],
    },
    {
      uuid: 2,
      target: 'my second column',
      sources: [],
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

  expect(screen.getAllByText(/akeneo.tailored_export.column_list.column_row.no_source/i)).toHaveLength(3);

  const firstInput = screen.getAllByPlaceholderText('akeneo.tailored_export.column_list.column_row.target_placeholder')[2];
  expect(firstInput).toHaveFocus();
});

test('it remove a column', () => {
  const columnsConfiguration = [
    {
      uuid: 1,
      target: 'my column',
      sources: [],
    },
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

  const removeButton = screen.getByTitle('akeneo.tailored_export.column_list.column_row.remove');
  fireEvent.click(removeButton);

  expect(handleRemove).toBeCalled();
});

test('it create a new column', () => {
  const columnsConfiguration = [
    {
      uuid: 1,
      target: 'my column',
      sources: [],
    },
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

  const lastInput = screen.getAllByPlaceholderText('akeneo.tailored_export.column_list.column_row.target_placeholder')[1];
  userEvent.type(lastInput, 'm');

  expect(handleCreate).toBeCalledWith('m');
});

test('it update a column', () => {
  const columnsConfiguration = [
    {
      uuid: 1,
      target: 'my column',
      sources: [],
    },
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

  const firstInput = screen.getAllByPlaceholderText('akeneo.tailored_export.column_list.column_row.target_placeholder')[0];
  fireEvent.change(firstInput, {target: {value: 'my new column name'}});

  expect(handleColumnChange).toBeCalledWith({
    uuid: 1,
    target: 'my new column name',
    sources: [],
  });
});

test('it displays validation errors', () => {
  const columnsConfiguration = [
    {
      uuid: 1,
      target: 'my column',
      sources: [],
    },
  ];

  const handleColumnChange = jest.fn();

  renderWithProviders(

    <ValidationErrorsContext.Provider value={[
      {
        messageTemplate: 'akeneo.tailored_export.validation.columns.target.max_length_reached',
        parameters: {
          '{{ value }}': 'way too long',
          '{{ limit }}': 255,
        },
        message: 'akeneo.tailored_export.validation.columns.target.max_length_reached',
        propertyPath: '[columns][1][target]',
        invalidValue: 'way too long',
      },
      {
        messageTemplate: 'akeneo.tailored_export.validation.columns.max_column_count',
        parameters: {},
        message: 'akeneo.tailored_export.validation.columns.max_column_count',
        propertyPath: '[columns]',
        invalidValue: '',
      }
    ]}>
      <ColumnList
        columnsConfiguration={columnsConfiguration}
        onColumnChange={handleColumnChange}
        onColumnCreated={jest.fn}
        onColumnRemoved={jest.fn}
        onColumnSelected={jest.fn}
        selectedColumn={null}
      />
    </ValidationErrorsContext.Provider>
  );

  const validationError = screen.getByText('akeneo.tailored_export.validation.columns.target.max_length_reached');
  expect(validationError).toBeInTheDocument();

  const globalError = screen.getByText('akeneo.tailored_export.validation.columns.max_column_count');
  expect(globalError).toBeInTheDocument();
});

test('it move to next line when user type enter', async () => {
  const columnsConfiguration = [
    {
      uuid: 1,
      target: 'my column',
      sources: [],
    },
    {
      uuid: 2,
      target: 'another column',
      sources: [],
    },
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

  const firstInput = screen.getAllByPlaceholderText('akeneo.tailored_export.column_list.column_row.target_placeholder')[0];
  userEvent.type(firstInput, '{enter}');

  expect(handleColumnSelected).toHaveBeenCalledWith(2);
});

test('it focus the selected column', async () => {
  const columnsConfiguration = [
    {
      uuid: 1,
      target: 'my column',
      sources: [],
    },
    {
      uuid: 2,
      target: 'another column',
      sources: [],
    },
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

  const firstInput = screen.getAllByPlaceholderText('akeneo.tailored_export.column_list.column_row.target_placeholder')[0];

  expect(firstInput).toHaveFocus();
});
