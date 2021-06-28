import React, {ReactNode} from 'react';
import {act, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders as baseRender, Channel} from '@akeneo-pim-community/shared';
import {ColumnDetails} from './ColumnDetails';
import {Attribute, AvailableSourceGroup, ColumnConfiguration} from '../../models';
import {FetcherContext, ValidationErrorsContext} from '../../contexts';

const attributes: Attribute[] = [
  {
    code: 'description',
    type: 'pim_catalog_text',
    labels: {},
    scopable: false,
    localizable: false,
    is_locale_specific: false,
    available_locales: [],
  },
];

const channels: Channel[] = [
  {
    code: 'ecommerce',
    labels: {},
    locales: [
      {
        code: 'en_US',
        label: 'en_US',
        region: 'US',
        language: 'en',
      },
      {
        code: 'br_FR',
        label: 'Breton',
        region: 'bzh',
        language: 'br',
      },
    ],
    category_tree: '',
    conversion_units: [],
    currencies: [],
    meta: {
      created: '',
      form: '',
      id: 1,
      updated: '',
    },
  },
];

const fetchers = {
  attribute: {fetchByIdentifiers: (): Promise<Attribute[]> => Promise.resolve<Attribute[]>(attributes)},
  channel: {fetchAll: (): Promise<Channel[]> => Promise.resolve(channels)},
};

const renderWithProviders = async (node: ReactNode) =>
  await act(async () => void baseRender(<FetcherContext.Provider value={fetchers}>{node}</FetcherContext.Provider>));

jest.mock('akeneo-design-system/lib/shared/uuid', () => ({
  uuid: () => '276b6361-badb-48a1-98ef-d75baa235148',
}));

jest.mock('../../hooks/useAvailableSourcesFetcher', () => ({
  useAvailableSourcesFetcher: () => (): AvailableSourceGroup[] =>
    [
      {
        code: 'system',
        label: 'System',
        children: [
          {
            code: 'categories',
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

test('it renders column details', async () => {
  const columnConfiguration: ColumnConfiguration = {
    uuid: '3a6645e0-0d70-411d-84ee-79833144544a',
    sources: [
      {
        uuid: 'cffd560e-1e40-4c55-a415-89c7958b270d',
        code: 'Description',
        type: 'attribute',
        locale: null,
        channel: null,
        operations: {},
        selection: {
          type: 'code',
        },
      },
    ],
    target: 'My column name',
    format: {
      type: 'concat',
      elements: [],
    },
  };

  await act(async () => {
    renderWithProviders(
      <FetcherContext.Provider value={fetchers}>
        <ColumnDetails columnConfiguration={columnConfiguration} onColumnChange={jest.fn} />
      </FetcherContext.Provider>
    );
  });

  expect(screen.getByText(/akeneo.tailored_export.column_details.sources.title/i)).toBeInTheDocument();
});

test('it renders placeholder when there is no source selected', async () => {
  const columnConfiguration: ColumnConfiguration = {
    uuid: '3a6645e0-0d70-411d-84ee-79833144544a',
    sources: [],
    target: 'My column name',
    format: {
      type: 'concat',
      elements: [],
    },
  };

  await act(async () => {
    renderWithProviders(
      <FetcherContext.Provider value={fetchers}>
        <ColumnDetails columnConfiguration={columnConfiguration} onColumnChange={jest.fn} />
      </FetcherContext.Provider>
    );
  });

  expect(
    screen.getByText(/akeneo.tailored_export.column_details.sources.no_source_selected.title/i)
  ).toBeInTheDocument();
});

test('We can add an attribute source', async () => {
  const columnConfiguration: ColumnConfiguration = {
    uuid: '3a6645e0-0d70-411d-84ee-79833144544a',
    sources: [],
    target: 'My column name',
    format: {
      type: 'concat',
      elements: [],
    },
  };

  const handleColumnsConfigurationChange = jest.fn();

  await act(async () => {
    renderWithProviders(
      <FetcherContext.Provider value={fetchers}>
        <ColumnDetails columnConfiguration={columnConfiguration} onColumnChange={handleColumnsConfigurationChange} />
      </FetcherContext.Provider>
    );
  });

  const addSourceButton = screen.getByText('akeneo.tailored_export.column_details.sources.add');
  await act(async () => {
    userEvent.click(addSourceButton);
  });

  await act(async () => {
    userEvent.click(screen.getByText('Description'));
  });

  expect(handleColumnsConfigurationChange).toHaveBeenCalledWith({
    target: 'My column name',
    uuid: '3a6645e0-0d70-411d-84ee-79833144544a',
    sources: [
      {
        channel: null,
        code: 'description',
        locale: null,
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
  });
});

test('We can add a property source', async () => {
  const columnConfiguration: ColumnConfiguration = {
    uuid: '3a6645e0-0d70-411d-84ee-79833144544a',
    sources: [],
    target: 'My column name',
    format: {
      type: 'concat',
      elements: [],
    },
  };

  const handleColumnsConfigurationChange = jest.fn();

  await act(async () => {
    renderWithProviders(
      <FetcherContext.Provider value={fetchers}>
        <ColumnDetails columnConfiguration={columnConfiguration} onColumnChange={handleColumnsConfigurationChange} />
      </FetcherContext.Provider>
    );
  });

  const addSourceButton = screen.getByText('akeneo.tailored_export.column_details.sources.add');
  await act(async () => {
    userEvent.click(addSourceButton);
  });

  await act(async () => {
    userEvent.click(screen.getByText('Categories'));
  });

  expect(handleColumnsConfigurationChange).toHaveBeenCalledWith({
    target: 'My column name',
    uuid: '3a6645e0-0d70-411d-84ee-79833144544a',
    sources: [
      {
        channel: null,
        code: 'categories',
        locale: null,
        operations: {},
        selection: {
          type: 'code',
          separator: ',',
        },
        type: 'property',
        uuid: '276b6361-badb-48a1-98ef-d75baa235148',
      },
    ],
    format: {
      elements: [],
      type: 'concat',
    },
  });
});

test('We can update a source', async () => {
  const columnConfiguration: ColumnConfiguration = {
    uuid: '3a6645e0-0d70-411d-84ee-79833144544a',
    sources: [
      {
        channel: null,
        code: 'name',
        locale: 'en_US',
        operations: {},
        selection: {
          type: 'code',
        },
        type: 'attribute',
        uuid: '266b6361-badb-48a1-98ef-d75baa235148',
      },
    ],
    target: 'My column name',
    format: {
      type: 'concat',
      elements: [],
    },
  };

  const handleColumnsConfigurationChange = jest.fn();

  await act(async () => {
    renderWithProviders(
      <FetcherContext.Provider value={fetchers}>
        <ColumnDetails columnConfiguration={columnConfiguration} onColumnChange={handleColumnsConfigurationChange} />
      </FetcherContext.Provider>
    );
  });

  const openLocaleDropdownButton = screen.getByLabelText('pim_common.locale');
  await act(async () => {
    userEvent.click(openLocaleDropdownButton);
  });

  await act(async () => {
    userEvent.click(screen.getByText('Breton'));
  });

  expect(handleColumnsConfigurationChange).toHaveBeenCalledWith({
    uuid: '3a6645e0-0d70-411d-84ee-79833144544a',
    sources: [
      {
        channel: null,
        code: 'name',
        locale: 'br_FR',
        operations: {},
        selection: {
          type: 'code',
        },
        type: 'attribute',
        uuid: '266b6361-badb-48a1-98ef-d75baa235148',
      },
    ],
    target: 'My column name',
    format: {
      type: 'concat',
      elements: [],
    },
  });
});

test('We can delete a source', async () => {
  const columnConfiguration: ColumnConfiguration = {
    uuid: '3a6645e0-0d70-411d-84ee-79833144544a',
    sources: [
      {
        channel: null,
        code: 'description',
        locale: 'en_US',
        operations: {},
        selection: {
          type: 'code',
        },
        type: 'attribute',
        uuid: '266b6361-badb-48a1-98ef-d75baa235148',
      },
    ],
    target: 'My column name',
    format: {
      type: 'concat',
      elements: [],
    },
  };

  const handleColumnsConfigurationChange = jest.fn();

  await act(async () => {
    renderWithProviders(
      <FetcherContext.Provider value={fetchers}>
        <ColumnDetails columnConfiguration={columnConfiguration} onColumnChange={handleColumnsConfigurationChange} />
      </FetcherContext.Provider>
    );
  });

  const removeButton = screen.getByText('akeneo.tailored_export.column_details.sources.remove.button');
  userEvent.click(removeButton);

  const confirmButton = screen.getByText('pim_common.delete');
  userEvent.click(confirmButton);

  expect(handleColumnsConfigurationChange).toHaveBeenCalledWith({
    uuid: '3a6645e0-0d70-411d-84ee-79833144544a',
    sources: [],
    target: 'My column name',
    format: {
      type: 'concat',
      elements: [],
    },
  });
});

test('it renders column details with errors', async () => {
  const columnConfiguration: ColumnConfiguration = {
    uuid: '3a6645e0-0d70-411d-84ee-79833144544a',
    sources: [
      {
        uuid: 'cffd560e-1e40-4c55-a415-89c7958b270d',
        code: 'Description',
        type: 'attribute',
        locale: null,
        channel: null,
        operations: {},
        selection: {
          type: 'code',
        },
      },
    ],
    target: 'My column name',
    format: {
      type: 'concat',
      elements: [],
    },
  };

  await act(async () => {
    renderWithProviders(
      <FetcherContext.Provider value={fetchers}>
        <ValidationErrorsContext.Provider
          value={[
            {
              messageTemplate: 'akeneo.tailored_export.validation.sources.max_source_count_reached',
              parameters: {'{{ count }}': 5, '{{ limit }}': 4},
              message: 'akeneo.tailored_export.validation.sources.max_source_count_reached',
              propertyPath: '[columns][3a6645e0-0d70-411d-84ee-79833144544a][sources]',
              invalidValue: [],
            },
          ]}
        >
          <ColumnDetails columnConfiguration={columnConfiguration} onColumnChange={jest.fn} />
        </ValidationErrorsContext.Provider>
      </FetcherContext.Provider>
    );
  });

  expect(screen.getByText(/akeneo.tailored_export.validation.sources.max_source_count_reached/i)).toBeInTheDocument();
});
