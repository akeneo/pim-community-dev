import React from 'react';
import {act, screen} from '@testing-library/react';
import {renderWithProviders, Channel} from '@akeneo-pim-community/shared';
import {ColumnDetails} from './ColumnDetails';
import {AvailableSourceGroup, ColumnConfiguration} from '../../models';
import {FetcherContext, Attribute, ValidationErrorsContext} from '../../contexts';
import userEvent from '@testing-library/user-event';

const fetchers = {
  attribute: {
    fetchByIdentifiers: (): Promise<Attribute[]> =>
      new Promise(resolve => {
        act(() => {
          resolve([{code: 'description', labels: {}, scopable: false, localizable: false}]);
        });
      }),
  },
  channel: {
    fetchAll: (): Promise<Channel[]> =>
      new Promise(resolve => {
        act(() => {
          resolve([
            {
              code: 'Ecommerce',
              labels: {},
              locales: [
                {
                  code: 'en_US',
                  label: 'English',
                  region: '',
                  language: '',
                },
                {
                  code: 'br_FR',
                  label: 'Breton',
                  region: '',
                  language: '',
                },
              ],
            },
          ]);
        });
      }),
  },
};

jest.mock('akeneo-design-system/lib/shared/uuid', () => ({
  uuid: () => '276b6361-badb-48a1-98ef-d75baa235148',
}));

global.beforeEach(() => {
  const intersectionObserverMock = () => ({
    observe: jest.fn(),
    unobserve: jest.fn(),
  });

  window.IntersectionObserver = jest.fn().mockImplementation(intersectionObserverMock);
});

jest.mock('../../hooks/useAvailableSourcesFetcher', () => ({
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
        operations: [],
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
    await userEvent.click(addSourceButton);
  });

  await act(async () => {
    await userEvent.click(screen.getByText('Description'));
  });

  expect(handleColumnsConfigurationChange).toHaveBeenCalledWith({
    target: 'My column name',
    uuid: '3a6645e0-0d70-411d-84ee-79833144544a',
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
    await userEvent.click(addSourceButton);
  });

  await act(async () => {
    await userEvent.click(screen.getByText('Categories'));
  });

  expect(handleColumnsConfigurationChange).toHaveBeenCalledWith({
    target: 'My column name',
    uuid: '3a6645e0-0d70-411d-84ee-79833144544a',
    sources: [
      {
        channel: null,
        code: 'category',
        locale: null,
        operations: [],
        selection: {
          type: 'code',
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

test('We can udpate a source', async () => {
  const columnConfiguration: ColumnConfiguration = {
    uuid: '3a6645e0-0d70-411d-84ee-79833144544a',
    sources: [
      {
        channel: null,
        code: 'category',
        locale: 'en_US',
        operations: [],
        selection: {
          type: 'code',
        },
        type: 'property',
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
    await userEvent.click(openLocaleDropdownButton);
  });

  await act(async () => {
    await userEvent.click(screen.getByText('Breton'));
  });

  expect(handleColumnsConfigurationChange).toHaveBeenCalledWith({
    uuid: '3a6645e0-0d70-411d-84ee-79833144544a',
    sources: [
      {
        channel: null,
        code: 'category',
        locale: 'br_FR',
        operations: [],
        selection: {
          type: 'code',
        },
        type: 'property',
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
        code: 'category',
        locale: 'en_US',
        operations: [],
        selection: {
          type: 'code',
        },
        type: 'property',
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
        operations: [],
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
