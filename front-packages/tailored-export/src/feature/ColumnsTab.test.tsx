import React from 'react';
import {screen} from '@testing-library/react';
import {ColumnsTab} from './ColumnsTab';
import userEvent from '@testing-library/user-event';
import {renderWithProviders, Channel} from '@akeneo-pim-community/shared';
import {fireEvent} from '@testing-library/dom';
import {ColumnConfiguration} from './models/ColumnConfiguration';
import {AvailableSourceGroup} from './models';
import {act} from 'react-dom/test-utils';
import {Attribute, FetcherContext} from './contexts';

const attributes = [{code: 'description', labels: {}, scopable: false, localizable: false}];
const fetchers = {
  attribute: {fetchByIdentifiers: (): Promise<Attribute[]> => Promise.resolve<Attribute[]>(attributes)},
  channel: {fetchAll: (): Promise<Channel[]> => Promise.resolve([])},
};

global.beforeEach(() => {
  const intersectionObserverMock = () => ({
    observe: jest.fn(),
    unobserve: jest.fn(),
  });

  window.IntersectionObserver = jest.fn().mockImplementation(intersectionObserverMock);
});

jest.mock('akeneo-design-system/lib/shared/uuid', () => ({
  uuid: () => '276b6361-badb-48a1-98ef-d75baa235148',
}));

jest.mock('./hooks/useAvailableSourcesFetcher', () => ({
  useAvailableSourcesFetcher: () => (): AvailableSourceGroup[] => [
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
}));

test('It open the source panel related to the column selected', () => {
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

  renderWithProviders(
    <FetcherContext.Provider value={fetchers}>
      <ColumnsTab
        columnsConfiguration={columnsConfiguration}
        validationErrors={[]}
        onColumnsConfigurationChange={jest.fn}
      />
    </FetcherContext.Provider>
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

test('It create a column when user enter a text in last input', () => {
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

  renderWithProviders(
    <FetcherContext.Provider value={fetchers}>
      <ColumnsTab
        columnsConfiguration={columnsConfiguration}
        validationErrors={[]}
        onColumnsConfigurationChange={handleColumnsConfigurationChange}
      />
    </FetcherContext.Provider>
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

  renderWithProviders(
    <FetcherContext.Provider value={fetchers}>
      <ColumnsTab
        columnsConfiguration={columnsConfiguration}
        validationErrors={[]}
        onColumnsConfigurationChange={handleColumnsConfigurationChange}
      />
    </FetcherContext.Provider>
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

test('It delete column when user click on delete button', () => {
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

  renderWithProviders(
    <FetcherContext.Provider value={fetchers}>
      <ColumnsTab
        columnsConfiguration={columnsConfiguration}
        validationErrors={[]}
        onColumnsConfigurationChange={handleColumnsConfigurationChange}
      />
    </FetcherContext.Provider>
  );

  const removeButton = screen.getByTitle('akeneo.tailored_export.column_list.column_row.remove');
  userEvent.click(removeButton);

  const confirmButton = screen.getByText('pim_common.delete');
  userEvent.click(confirmButton);

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

  renderWithProviders(
    <FetcherContext.Provider value={fetchers}>
      <ColumnsTab
        columnsConfiguration={columnsConfiguration}
        validationErrors={[]}
        onColumnsConfigurationChange={handleColumnsConfigurationChange}
      />
    </FetcherContext.Provider>
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
          channel: null,
          code: 'description',
          locale: null,
          operations: [],
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
