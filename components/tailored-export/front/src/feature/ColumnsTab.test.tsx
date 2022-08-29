import React from 'react';
import {screen, fireEvent, act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {ColumnsTab} from './ColumnsTab';
import {ColumnConfiguration} from './models/ColumnConfiguration';
import {renderWithProviders} from './tests';

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

test('It opens the source panel related to the column selected', async () => {
  const columnsConfiguration: ColumnConfiguration[] = [
    {
      uuid: 'fbf9cff9-e95c-4e7d-983b-2947c7df90df',
      target: 'my column',
      sources: [],
      format: {
        type: 'concat',
        elements: [],
        space_between: true,
      },
    },
  ];

  await renderWithProviders(
    <ColumnsTab
      entityType="product"
      columnsConfiguration={columnsConfiguration}
      validationErrors={[]}
      onColumnsConfigurationChange={jest.fn()}
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

test('It creates a column when user enter a text in last input', async () => {
  const columnsConfiguration: ColumnConfiguration[] = [
    {
      uuid: 'fbf9cff9-e95c-4e7d-983b-2947c7df90df',
      target: 'my column',
      sources: [],
      format: {
        elements: [],
        type: 'concat',
        space_between: true,
      },
    },
  ];

  const handleColumnsConfigurationChange = jest.fn();

  await renderWithProviders(
    <ColumnsTab
      entityType="product"
      columnsConfiguration={columnsConfiguration}
      validationErrors={[]}
      onColumnsConfigurationChange={handleColumnsConfigurationChange}
    />
  );

  act(() => {
    const lastInput = screen.getAllByPlaceholderText(
      'akeneo.tailored_export.column_list.column_row.target_placeholder'
    )[1];
    userEvent.type(lastInput, 't');
  });

  expect(handleColumnsConfigurationChange).toHaveBeenCalledWith([
    {
      uuid: expect.any(String),
      target: 'my column',
      sources: [],
      format: {
        elements: [],
        type: 'concat',
        space_between: true,
      },
    },
    {
      uuid: expect.any(String),
      target: 't',
      sources: [],
      format: {
        elements: [],
        type: 'concat',
        space_between: true,
      },
    },
  ]);
});

test('It updates column when user change value input', async () => {
  const columnsConfiguration: ColumnConfiguration[] = [
    {
      uuid: 'fbf9cff9-e95c-4e7d-983b-2947c7df90df',
      target: 'my column',
      sources: [],
      format: {
        elements: [],
        type: 'concat',
        space_between: true,
      },
    },
  ];

  const handleColumnsConfigurationChange = jest.fn();

  await renderWithProviders(
    <ColumnsTab
      entityType="product"
      columnsConfiguration={columnsConfiguration}
      validationErrors={[]}
      onColumnsConfigurationChange={handleColumnsConfigurationChange}
    />
  );

  const firstInput = screen.getAllByPlaceholderText(
    'akeneo.tailored_export.column_list.column_row.target_placeholder'
  )[0];

  act(() => {
    fireEvent.change(firstInput, {target: {value: 'my new column name'}});
  });

  expect(handleColumnsConfigurationChange).toHaveBeenCalledWith([
    {
      uuid: 'fbf9cff9-e95c-4e7d-983b-2947c7df90df',
      target: 'my new column name',
      sources: [],
      format: {
        elements: [],
        type: 'concat',
        space_between: true,
      },
    },
  ]);
});

test('It deletes column when user click on delete button', async () => {
  const columnsConfiguration: ColumnConfiguration[] = [
    {
      uuid: 'fbf9cff9-e95c-4e7d-983b-2947c7df90df',
      target: 'my column',
      sources: [],
      format: {
        elements: [],
        type: 'concat',
        space_between: true,
      },
    },
  ];

  const handleColumnsConfigurationChange = jest.fn();

  await renderWithProviders(
    <ColumnsTab
      entityType="product"
      columnsConfiguration={columnsConfiguration}
      validationErrors={[]}
      onColumnsConfigurationChange={handleColumnsConfigurationChange}
    />
  );

  userEvent.click(screen.getByTitle('akeneo.tailored_export.column_list.column_row.remove'));
  userEvent.click(screen.getByText('pim_common.confirm'));

  expect(handleColumnsConfigurationChange).toHaveBeenCalledWith([]);
});

test('It adds source when user click on add source', async () => {
  const columnsConfiguration: ColumnConfiguration[] = [
    {
      uuid: 'fbf9cff9-e95c-4e7d-983b-2947c7df90df',
      target: 'my column',
      sources: [],
      format: {
        elements: [],
        type: 'concat',
        space_between: true,
      },
    },
  ];

  const handleColumnsConfigurationChange = jest.fn();

  await renderWithProviders(
    <ColumnsTab
      entityType="product"
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
          uuid: expect.any(String),
        },
      ],
      format: {
        elements: [
          {
            type: 'source',
            uuid: expect.any(String),
            value: expect.any(String),
          },
        ],
        type: 'concat',
        space_between: true,
      },
    },
  ]);
});
