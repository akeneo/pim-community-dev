import React from 'react';
import {screen} from '@testing-library/react';
import {ColumnsTab} from './ColumnsTab';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from './utils';
import {fireEvent} from '@testing-library/dom';

jest.mock('akeneo-design-system/lib/shared/uuid', () => ({
  uuid: () => '276b6361-badb-48a1-98ef-d75baa235148',
}));

test('It open the source panel related to the column selected', () => {
  const columnsConfiguration = [
    {
      uuid: 'fbf9cff9-e95c-4e7d-983b-2947c7df90df',
      target: 'my column',
      sources: []
    }
  ];

  renderWithProviders(
    <ColumnsTab
      columnsConfiguration={columnsConfiguration}
      onColumnsConfigurationChange={jest.fn}
    />
  );

  expect(screen.getByText('fbf9cff9-e95c-4e7d-983b-2947c7df90df')).toBeInTheDocument();

  const myColumnInput = screen.getAllByPlaceholderText('The column name')[0];
  userEvent.click(myColumnInput);

  const lastInput = screen.getAllByPlaceholderText('The column name')[1];
  userEvent.click(lastInput);

  expect(screen.getByText('No source selected for the moment.')).toBeInTheDocument();
});

test('It create a column when user enter a text in last input', () => {
  const columnsConfiguration = [
    {
      uuid: 'fbf9cff9-e95c-4e7d-983b-2947c7df90df',
      target: 'my column',
      sources: []
    }
  ];

  const handleColumnsConfigurationChange = jest.fn();

  renderWithProviders(
    <ColumnsTab
      columnsConfiguration={columnsConfiguration}
      onColumnsConfigurationChange={handleColumnsConfigurationChange}
    />
  );

  const lastInput = screen.getAllByPlaceholderText('The column name')[1];
  userEvent.type(lastInput, 't');

  expect(handleColumnsConfigurationChange).toHaveBeenCalledWith([
    {
      uuid: 'fbf9cff9-e95c-4e7d-983b-2947c7df90df',
      target: 'my column',
      sources: []
    },
    {
      uuid: '276b6361-badb-48a1-98ef-d75baa235148',
      target: 't',
      sources: [],
      format: {
        elements: [],
        type: "concat",
      },
    }
  ]);
});

test('It update column when user change value input', () => {
  const columnsConfiguration = [
    {
      uuid: 'fbf9cff9-e95c-4e7d-983b-2947c7df90df',
      target: 'my column',
      sources: []
    }
  ];

  const handleColumnsConfigurationChange = jest.fn();

  renderWithProviders(
    <ColumnsTab
      columnsConfiguration={columnsConfiguration}
      onColumnsConfigurationChange={handleColumnsConfigurationChange}
    />
  );

  const firstInput = screen.getAllByPlaceholderText('The column name')[0];
  fireEvent.change(firstInput, {target: {value: 'my new column name'}});

  expect(handleColumnsConfigurationChange).toHaveBeenCalledWith([
    {
      uuid: 'fbf9cff9-e95c-4e7d-983b-2947c7df90df',
      target: 'my new column name',
      sources: []
    },
  ]);
});

test('It delete column when user click on delete button', () => {
  const columnsConfiguration = [
    {
      uuid: 'fbf9cff9-e95c-4e7d-983b-2947c7df90df',
      target: 'my column',
      sources: []
    }
  ];

  const handleColumnsConfigurationChange = jest.fn();

  renderWithProviders(
    <ColumnsTab
      columnsConfiguration={columnsConfiguration}
      onColumnsConfigurationChange={handleColumnsConfigurationChange}
    />
  );

  const removeButton = screen.getByTitle('Remove column');
  userEvent.click(removeButton);

  expect(handleColumnsConfigurationChange).toHaveBeenCalledWith([]);
});
