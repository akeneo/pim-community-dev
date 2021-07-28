import React from 'react';
import {act, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {ColumnDetails} from './ColumnDetails';
import {ColumnConfiguration, Source} from '../../models';
import {ValidationErrorsContext} from '../../contexts';
import {renderWithProviders} from 'feature/tests';

jest.mock('akeneo-design-system/lib/shared/uuid', () => ({
  uuid: () => '276b6361-badb-48a1-98ef-d75baa235148',
}));

jest.mock('../../hooks/useAvailableSourcesFetcher', () => ({
  useAvailableSourcesFetcher: () => () => ({
    results: [
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
        code: 'association_types',
        label: 'Association types',
        children: [
          {
            code: 'XSELL',
            label: 'Cross sell',
            type: 'association_type',
          },
          {
            code: 'UPSELL',
            label: '[UPSELL]',
            type: 'association_type',
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

  await renderWithProviders(<ColumnDetails columnConfiguration={columnConfiguration} onColumnChange={jest.fn} />);

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

  await renderWithProviders(<ColumnDetails columnConfiguration={columnConfiguration} onColumnChange={jest.fn} />);

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

  await renderWithProviders(
    <ColumnDetails columnConfiguration={columnConfiguration} onColumnChange={handleColumnsConfigurationChange} />
  );

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
  });
});

test('We cannot add source when the limit is reached', async () => {
  const source: Source = {
    channel: null,
    code: 'description',
    locale: null,
    operations: {},
    selection: {
      type: 'code',
    },
    type: 'attribute',
    uuid: '276b6361-badb-48a1-98ef-d75baa235148',
  };

  const columnConfiguration: ColumnConfiguration = {
    uuid: '3a6645e0-0d70-411d-84ee-79833144544a',
    sources: [source, {...source, uuid: '1'}, {...source, uuid: '2'}, {...source, uuid: '3'}],
    target: 'My column name',
    format: {
      type: 'concat',
      elements: [],
    },
  };

  const handleColumnsConfigurationChange = jest.fn();

  await renderWithProviders(
    <ColumnDetails columnConfiguration={columnConfiguration} onColumnChange={handleColumnsConfigurationChange} />
  );

  const addSourceButton = screen.getByText('akeneo.tailored_export.column_details.sources.add');

  expect(addSourceButton).toHaveAttribute('disabled');
  expect(addSourceButton).toHaveAttribute(
    'title',
    'akeneo.tailored_export.validation.sources.max_source_count_reached'
  );

  await act(async () => {
    userEvent.click(addSourceButton);
  });

  expect(handleColumnsConfigurationChange).not.toHaveBeenCalled();
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

  await renderWithProviders(
    <ColumnDetails columnConfiguration={columnConfiguration} onColumnChange={handleColumnsConfigurationChange} />
  );

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

test('We can add an association type as source', async () => {
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

  await renderWithProviders(
    <ColumnDetails columnConfiguration={columnConfiguration} onColumnChange={handleColumnsConfigurationChange} />
  );

  const addSourceButton = screen.getByText('akeneo.tailored_export.column_details.sources.add');
  await act(async () => {
    userEvent.click(addSourceButton);
  });

  await act(async () => {
    userEvent.click(screen.getByText('Cross sell'));
  });

  expect(handleColumnsConfigurationChange).toHaveBeenCalledWith({
    target: 'My column name',
    uuid: '3a6645e0-0d70-411d-84ee-79833144544a',
    sources: [
      {
        uuid: '276b6361-badb-48a1-98ef-d75baa235148',
        type: 'association_type',
        code: 'XSELL',
        channel: null,
        locale: null,
        operations: {},
        selection: {
          type: 'code',
          separator: ',',
          entity_type: 'products',
        },
      },
    ],
    format: {
      type: 'concat',
      elements: [],
    },
  });
});

test('We can update a source', async () => {
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

  await renderWithProviders(
    <ColumnDetails columnConfiguration={columnConfiguration} onColumnChange={handleColumnsConfigurationChange} />
  );

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
        code: 'description',
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

  await renderWithProviders(
    <ColumnDetails columnConfiguration={columnConfiguration} onColumnChange={handleColumnsConfigurationChange} />
  );

  userEvent.click(screen.getByText('akeneo.tailored_export.column_details.sources.remove.button'));
  userEvent.click(screen.getByText('pim_common.confirm'));

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

  await renderWithProviders(
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
  );

  expect(screen.getByText(/akeneo.tailored_export.validation.sources.max_source_count_reached/i)).toBeInTheDocument();
});
