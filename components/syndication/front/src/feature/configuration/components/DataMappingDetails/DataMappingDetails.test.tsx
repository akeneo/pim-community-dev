import React from 'react';
import {act, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {DataMappingDetails} from './DataMappingDetails';
import {DataMapping, Source} from '../../models';
import {ValidationErrorsContext} from '../../contexts';
import {renderWithProviders} from '../../tests';
import {RequirementsProvider} from '../../contexts/RequirementsContext';

jest.mock('../../hooks/pim/useAvailableSourcesFetcher', () => ({
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
            code: 'text',
            label: 'Description',
            type: 'attribute',
          },
        ],
      },
    ],
  }),
}));

test('it renders dataMapping details', async () => {
  const dataMapping: DataMapping = {
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
    target: {
      name: 'text',
      type: 'string',
      required: false,
    },
    format: {
      type: 'concat',
      elements: [],
      space_between: true,
    },
  };
  const requirement = {
    code: 'text',
    label: 'text',
    help: '',
    group: '',
    examples: [],
    type: 'string' as const,
    required: false,
  };

  await renderWithProviders(
    <RequirementsProvider requirements={[requirement]}>
      <DataMappingDetails dataMapping={dataMapping} onDataMappingChange={jest.fn()} />
    </RequirementsProvider>
  );

  expect(screen.getByText('[Description]')).toBeInTheDocument();
});

test('it renders placeholder when there is no source selected', async () => {
  const dataMapping: DataMapping = {
    uuid: '3a6645e0-0d70-411d-84ee-79833144544a',
    sources: [],
    target: {
      name: 'text',
      type: 'string',
      required: false,
    },
    format: {
      type: 'concat',
      elements: [],
      space_between: true,
    },
  };
  const requirement = {
    code: 'text',
    label: 'text',
    help: '',
    group: '',
    examples: [],
    type: 'string' as const,
    required: false,
  };

  await renderWithProviders(
    <RequirementsProvider requirements={[requirement]}>
      <DataMappingDetails dataMapping={dataMapping} onDataMappingChange={jest.fn()} />
    </RequirementsProvider>
  );

  expect(
    screen.getByText(/akeneo.syndication.data_mapping_details.sources.no_source_selected.title/i)
  ).toBeInTheDocument();
});

test.skip('We can add an attribute source', async () => {
  const dataMapping: DataMapping = {
    uuid: '3a6645e0-0d70-411d-84ee-79833144544a',
    sources: [],
    target: {
      name: 'text',
      type: 'string',
      required: false,
    },
    format: {
      type: 'concat',
      elements: [],
      space_between: true,
    },
  };

  const handleDataMappingsConfigurationChange = jest.fn();
  const requirement = {
    code: 'text',
    label: 'text',
    help: '',
    group: '',
    examples: [],
    type: 'string' as const,
    required: false,
  };

  await renderWithProviders(
    <RequirementsProvider requirements={[requirement]}>
      <DataMappingDetails dataMapping={dataMapping} onDataMappingChange={handleDataMappingsConfigurationChange} />
    </RequirementsProvider>
  );

  const addSourceButton = screen.getByText('akeneo.syndication.data_mapping_details.sources.add');
  await act(async () => {
    userEvent.click(addSourceButton);
  });

  await act(async () => {
    userEvent.click(screen.getByText('text'));
  });

  expect(handleDataMappingsConfigurationChange).toHaveBeenCalledWith({
    target: {
      name: 'text',
      type: 'string',
      required: false,
    },
    uuid: '3a6645e0-0d70-411d-84ee-79833144544a',
    sources: [
      {
        channel: 'ecommerce',
        code: 'text',
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
  });
});

test('We cannot add source when the limit is reached', async () => {
  const source: Source = {
    channel: null,
    code: 'text',
    locale: null,
    operations: {},
    selection: {
      type: 'code',
    },
    type: 'attribute',
    uuid: '276b6361-badb-48a1-98ef-d75baa235148',
  };

  const dataMapping: DataMapping = {
    uuid: '3a6645e0-0d70-411d-84ee-79833144544a',
    sources: [source, {...source, uuid: '1'}, {...source, uuid: '2'}, {...source, uuid: '3'}],
    target: {
      name: 'text',
      type: 'string',
      required: false,
    },
    format: {
      type: 'concat',
      elements: [],
      space_between: true,
    },
  };

  const handleDataMappingsConfigurationChange = jest.fn();
  const requirement = {
    code: 'text',
    label: 'text',
    help: '',
    group: '',
    examples: [],
    type: 'string' as const,
    required: false,
  };

  await renderWithProviders(
    <RequirementsProvider requirements={[requirement]}>
      <DataMappingDetails dataMapping={dataMapping} onDataMappingChange={handleDataMappingsConfigurationChange} />
    </RequirementsProvider>
  );

  const addSourceButton = screen.getByText('akeneo.syndication.data_mapping_details.sources.add');

  expect(addSourceButton).toHaveAttribute('disabled');
  expect(addSourceButton).toHaveAttribute('title', 'akeneo.syndication.validation.sources.max_source_count_reached');

  await act(async () => {
    userEvent.click(addSourceButton);
  });

  expect(handleDataMappingsConfigurationChange).not.toHaveBeenCalled();
});

test('We can add a property source', async () => {
  const dataMapping: DataMapping = {
    uuid: '3a6645e0-0d70-411d-84ee-79833144544a',
    sources: [],
    target: {
      name: 'text',
      type: 'string',
      required: false,
    },
    format: {
      type: 'concat',
      elements: [],
      space_between: true,
    },
  };

  const handleDataMappingsConfigurationChange = jest.fn();
  const requirement = {
    code: 'text',
    label: 'text',
    help: '',
    group: '',
    examples: [],
    type: 'string' as const,
    required: false,
  };

  await renderWithProviders(
    <RequirementsProvider requirements={[requirement]}>
      <DataMappingDetails dataMapping={dataMapping} onDataMappingChange={handleDataMappingsConfigurationChange} />
    </RequirementsProvider>
  );

  const addSourceButton = screen.getByText('akeneo.syndication.data_mapping_details.sources.add');
  await act(async () => {
    userEvent.click(addSourceButton);
  });

  await act(async () => {
    userEvent.click(screen.getByText('Categories'));
  });

  expect(handleDataMappingsConfigurationChange).toHaveBeenCalledWith({
    target: {
      name: 'text',
      type: 'string',
      required: false,
    },
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
  });
});

test('We can add an association type as source', async () => {
  const dataMapping: DataMapping = {
    uuid: '3a6645e0-0d70-411d-84ee-79833144544a',
    sources: [],
    target: {
      name: 'text',
      type: 'string',
      required: false,
    },
    format: {
      type: 'concat',
      elements: [],
      space_between: true,
    },
  };

  const handleDataMappingsConfigurationChange = jest.fn();
  const requirement = {
    code: 'text',
    label: 'text',
    help: '',
    group: '',
    examples: [],
    type: 'string' as const,
    required: false,
  };

  await renderWithProviders(
    <RequirementsProvider requirements={[requirement]}>
      <DataMappingDetails dataMapping={dataMapping} onDataMappingChange={handleDataMappingsConfigurationChange} />
    </RequirementsProvider>
  );

  const addSourceButton = screen.getByText('akeneo.syndication.data_mapping_details.sources.add');
  await act(async () => {
    userEvent.click(addSourceButton);
  });

  await act(async () => {
    userEvent.click(screen.getByText('Cross sell'));
  });

  expect(handleDataMappingsConfigurationChange).toHaveBeenCalledWith({
    target: {
      name: 'text',
      type: 'string',
      required: false,
    },
    uuid: '3a6645e0-0d70-411d-84ee-79833144544a',
    sources: [
      {
        uuid: expect.any(String),
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
      elements: [
        {
          type: 'source',
          uuid: expect.any(String),
          value: expect.any(String),
        },
      ],
      space_between: true,
    },
  });
});

test.skip('We can update a source', async () => {
  const dataMapping: DataMapping = {
    uuid: '3a6645e0-0d70-411d-84ee-79833144544a',
    sources: [
      {
        channel: null,
        code: 'text',
        locale: 'en_US',
        operations: {},
        selection: {
          type: 'code',
        },
        type: 'attribute',
        uuid: expect.any(String),
      },
    ],
    target: {
      name: 'text',
      type: 'string',
      required: false,
    },
    format: {
      type: 'concat',
      elements: [],
      space_between: true,
    },
  };

  const handleDataMappingsConfigurationChange = jest.fn();
  const requirement = {
    code: 'text',
    label: 'text',
    help: '',
    group: '',
    examples: [],
    type: 'string' as const,
    required: false,
  };

  await renderWithProviders(
    <RequirementsProvider requirements={[requirement]}>
      <DataMappingDetails dataMapping={dataMapping} onDataMappingChange={handleDataMappingsConfigurationChange} />
    </RequirementsProvider>
  );

  const openLocaleDropdownButton = screen.getByLabelText('pim_common.locale');
  await act(async () => {
    userEvent.click(openLocaleDropdownButton);
  });

  await act(async () => {
    userEvent.click(screen.getByText('Breton'));
  });

  expect(handleDataMappingsConfigurationChange).toHaveBeenCalledWith({
    uuid: '3a6645e0-0d70-411d-84ee-79833144544a',
    sources: [
      {
        channel: null,
        code: 'text',
        locale: 'br_FR',
        operations: {},
        selection: {
          type: 'code',
        },
        type: 'attribute',
        uuid: '266b6361-badb-48a1-98ef-d75baa235148',
      },
    ],
    target: {
      name: 'text',
      type: 'string',
      required: false,
    },
    format: {
      type: 'concat',
      elements: [],
      space_between: true,
    },
  });
});

test('We can update the format', async () => {
  const dataMapping: DataMapping = {
    uuid: '3a6645e0-0d70-411d-84ee-79833144544a',
    sources: [
      {
        channel: null,
        code: 'text',
        locale: 'en_US',
        operations: {},
        selection: {
          type: 'code',
        },
        type: 'attribute',
        uuid: expect.any(String),
      },
    ],
    target: {
      name: 'text',
      type: 'string',
      required: false,
    },
    format: {
      type: 'concat',
      elements: [],
      space_between: true,
    },
  };

  const handleDataMappingsConfigurationChange = jest.fn();
  const requirement = {
    code: 'text',
    label: 'text',
    help: '',
    group: '',
    examples: [],
    type: 'string' as const,
    required: false,
  };

  await renderWithProviders(
    <RequirementsProvider requirements={[requirement]}>
      <DataMappingDetails dataMapping={dataMapping} onDataMappingChange={handleDataMappingsConfigurationChange} />
    </RequirementsProvider>
  );

  const spaceBetweenCheckbox = screen.getByLabelText(
    'akeneo.syndication.data_mapping_details.concatenation.space_between'
  );
  userEvent.click(spaceBetweenCheckbox);

  expect(handleDataMappingsConfigurationChange).toHaveBeenCalledWith({
    uuid: '3a6645e0-0d70-411d-84ee-79833144544a',
    sources: [
      {
        channel: null,
        code: 'text',
        locale: 'en_US',
        operations: {},
        selection: {
          type: 'code',
        },
        type: 'attribute',
        uuid: expect.any(String),
      },
    ],
    target: {
      name: 'text',
      type: 'string',
      required: false,
    },
    format: {
      type: 'concat',
      space_between: false,
      elements: [],
    },
  });
});

test('We can delete a source', async () => {
  const dataMapping: DataMapping = {
    uuid: '3a6645e0-0d70-411d-84ee-79833144544a',
    sources: [
      {
        channel: null,
        code: 'text',
        locale: 'en_US',
        operations: {},
        selection: {
          type: 'code',
        },
        type: 'attribute',
        uuid: '266b6361-badb-48a1-98ef-d75baa235148',
      },
    ],
    target: {
      name: 'text',
      type: 'string',
      required: false,
    },
    format: {
      type: 'concat',
      elements: [],
      space_between: true,
    },
  };

  const handleDataMappingsConfigurationChange = jest.fn();
  const requirement = {
    code: 'text',
    label: 'text',
    help: '',
    group: '',
    examples: [],
    type: 'string' as const,
    required: false,
  };

  await renderWithProviders(
    <RequirementsProvider requirements={[requirement]}>
      <DataMappingDetails dataMapping={dataMapping} onDataMappingChange={handleDataMappingsConfigurationChange} />
    </RequirementsProvider>
  );

  userEvent.click(screen.getByText('akeneo.syndication.data_mapping_details.sources.remove.button'));
  userEvent.click(screen.getByText('pim_common.confirm'));

  expect(handleDataMappingsConfigurationChange).toHaveBeenCalledWith({
    uuid: '3a6645e0-0d70-411d-84ee-79833144544a',
    sources: [],
    target: {
      name: 'text',
      type: 'string',
      required: false,
    },
    format: {
      type: 'concat',
      elements: [],
      space_between: true,
    },
  });
});

test.skip('it renders dataMapping details with errors', async () => {
  const dataMapping: DataMapping = {
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
    target: {
      name: 'text',
      type: 'string',
      required: false,
    },
    format: {
      type: 'concat',
      elements: [],
      space_between: true,
    },
  };
  const requirement = {
    code: 'Description',
    label: 'text',
    help: '',
    group: '',
    examples: [],
    type: 'string' as const,
    required: false,
  };

  await renderWithProviders(
    <ValidationErrorsContext.Provider
      value={[
        {
          messageTemplate: 'akeneo.syndication.validation.sources.max_source_count_reached',
          parameters: {'{{ count }}': 5, '{{ limit }}': 4},
          message: 'akeneo.syndication.validation.sources.max_source_count_reached',
          propertyPath: '[dataMappings][3a6645e0-0d70-411d-84ee-79833144544a][sources]',
          invalidValue: [],
        },
      ]}
    >
      <RequirementsProvider requirements={[requirement]}>
        <DataMappingDetails dataMapping={dataMapping} onDataMappingChange={jest.fn()} />
      </RequirementsProvider>
    </ValidationErrorsContext.Provider>
  );

  expect(screen.getByText(/akeneo.syndication.validation.sources.max_source_count_reached/i)).toBeInTheDocument();
});
