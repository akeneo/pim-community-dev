import React from 'react';
import {screen, fireEvent, act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {ColumnsTab} from './ColumnsTab';
import {ColumnConfiguration} from './models/ColumnConfiguration';
import {renderWithProviders} from './tests';

jest.mock('akeneo-design-system/lib/shared/uuid', () => ({
  uuid: () => '276b6361-badb-48a1-98ef-d75baa235148',
}));

jest.mock('./hooks/useAvailableSourcesFetcher', () => ({
  useAvailableSourcesFetcher: () => () => ({
    results: [
      {
        code: 'system',
        label: 'System',
        children: [
          {
            code: 'category',
            label: 'Categories',
            type: 'property',
          },
          {
            code: 'enabled',
            label: 'Activé',
            type: 'property',
          },
        ],
      },
      {
        code: 'marketing',
        label: 'Marketing',
        children: [
          {
            code: 'name',
            label: 'Nom',
            type: 'attribute',
          },
          {
            code: 'description',
            label: 'Description',
            type: 'attribute',
          },
        ],
      },
    ],
  }),
}));

test('It open the source panel related to the column selected', async () => {
  const columnsConfiguration: ColumnConfiguration[] = [
    {
      uuid: 'fbf9cff9-e95c-4e7d-983b-2947c7df90df',
      target: 'my column',
      sources: [],
      format: {
        type: 'concat',
        elements: [],
      },
    },
  ];

  await renderWithProviders(
    <ColumnsTab
      columnsConfiguration={columnsConfiguration}
      validationErrors={[]}
      onColumnsConfigurationChange={jest.fn}
    />
  );

  const myColumnInput = screen.getAllByPlaceholderText(
    'akeneo.tailored_export.column_list.column_row.target_placeholder'
  )[0];
  userEvent.click(myColumnInput);

  const lastInput = screen.getAllByPlaceholderText(
    'akeneo.tailored_export.column_list.column_row.target_placeholder'
  )[1];
  userEvent.click(lastInput);

  expect(
    screen.getByText('akeneo.tailored_export.column_details.sources.no_column_selected.title')
  ).toBeInTheDocument();
});

test('It create a column when user enter a text in last input', async () => {
  const columnsConfiguration: ColumnConfiguration[] = [
    {
      uuid: 'fbf9cff9-e95c-4e7d-983b-2947c7df90df',
      target: 'my column',
      sources: [],
      format: {
        elements: [],
        type: 'concat',
      },
    },
  ];

  const handleColumnsConfigurationChange = jest.fn();

  await renderWithProviders(
    <ColumnsTab
      columnsConfiguration={columnsConfiguration}
      validationErrors={[]}
      onColumnsConfigurationChange={handleColumnsConfigurationChange}
    />
  );

  const lastInput = screen.getAllByPlaceholderText(
    'akeneo.tailored_export.column_list.column_row.target_placeholder'
  )[1];
  userEvent.type(lastInput, 't');

  expect(handleColumnsConfigurationChange).toHaveBeenCalledWith([
    {
      uuid: 'fbf9cff9-e95c-4e7d-983b-2947c7df90df',
      target: 'my column',
      sources: [],
      format: {
        elements: [],
        type: 'concat',
      },
    },
    {
      uuid: '276b6361-badb-48a1-98ef-d75baa235148',
      target: 't',
      sources: [],
      format: {
        elements: [],
        type: 'concat',
      },
    },
  ]);
});

test('It update column when user change value input', async () => {
  const columnsConfiguration: ColumnConfiguration[] = [
    {
      uuid: 'fbf9cff9-e95c-4e7d-983b-2947c7df90df',
      target: 'my column',
      sources: [],
      format: {
        elements: [],
        type: 'concat',
      },
    },
  ];

  const handleColumnsConfigurationChange = jest.fn();

  await renderWithProviders(
    <ColumnsTab
      columnsConfiguration={columnsConfiguration}
      validationErrors={[]}
      onColumnsConfigurationChange={handleColumnsConfigurationChange}
    />
  );

  const firstInput = screen.getAllByPlaceholderText(
    'akeneo.tailored_export.column_list.column_row.target_placeholder'
  )[0];

  await act(async () => {
    await fireEvent.change(firstInput, {target: {value: 'my new column name'}});
  });

  expect(handleColumnsConfigurationChange).toHaveBeenCalledWith([
    {
      uuid: 'fbf9cff9-e95c-4e7d-983b-2947c7df90df',
      target: 'my new column name',
      sources: [],
      format: {
        elements: [],
        type: 'concat',
      },
    },
  ]);
});

test('It delete column when user click on delete button', async () => {
  const columnsConfiguration: ColumnConfiguration[] = [
    {
      uuid: 'fbf9cff9-e95c-4e7d-983b-2947c7df90df',
      target: 'my column',
      sources: [],
      format: {
        elements: [],
        type: 'concat',
      },
    },
  ];

  const handleColumnsConfigurationChange = jest.fn();

  await renderWithProviders(
    <ColumnsTab
      columnsConfiguration={columnsConfiguration}
      validationErrors={[]}
      onColumnsConfigurationChange={handleColumnsConfigurationChange}
    />
  );

  userEvent.click(screen.getByTitle('akeneo.tailored_export.column_list.column_row.remove'));
  userEvent.click(screen.getByText('pim_common.confirm'));

  expect(handleColumnsConfigurationChange).toHaveBeenCalledWith([]);
});

test('It add source when user click on add source', async () => {
  const columnsConfiguration: ColumnConfiguration[] = [
    {
      uuid: 'fbf9cff9-e95c-4e7d-983b-2947c7df90df',
      target: 'my column',
      sources: [],
      format: {
        elements: [],
        type: 'concat',
      },
    },
  ];

  const handleColumnsConfigurationChange = jest.fn();

  await renderWithProviders(
    <ColumnsTab
      columnsConfiguration={columnsConfiguration}
      validationErrors={[]}
      onColumnsConfigurationChange={handleColumnsConfigurationChange}
    />
  );

  const addSourceButton = screen.getByText('akeneo.tailored_export.column_details.sources.add');
  await act(async () => {
    userEvent.click(addSourceButton);
  });

  await act(async () => {
    userEvent.click(screen.getByText('Description'));
  });

  expect(handleColumnsConfigurationChange).toHaveBeenCalledWith([
    {
      target: 'my column',
      uuid: 'fbf9cff9-e95c-4e7d-983b-2947c7df90df',
      sources: [
        {
          channel: 'ecommerce',
          code: 'description',
          locale: 'en_US',
          operations: {},
          selection: {
            type: 'code',
          },
          type: 'attribute',
          uuid: '276b6361-badb-48a1-98ef-d75baa235148',
        },
      ],
      format: {
        elements: [],
        type: 'concat',
      },
    },
  ]);
});
