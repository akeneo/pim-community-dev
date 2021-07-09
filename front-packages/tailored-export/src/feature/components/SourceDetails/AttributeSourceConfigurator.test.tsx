import React, {ReactNode} from 'react';
import {screen, act} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders as baseRender, Channel} from '@akeneo-pim-community/shared';
import {AttributeSourceConfigurator} from './AttributeSourceConfigurator';
import {Attribute, Source} from '../../models';
import {FetcherContext} from '../../contexts';

const attributes = [
  {
    code: 'description',
    type: 'pim_catalog_text',
    labels: {},
    scopable: false,
    localizable: false,
    is_locale_specific: false,
    available_locales: [],
  },
  {
    code: 'locale_specific',
    type: 'pim_catalog_text',
    labels: {},
    scopable: false,
    localizable: false,
    is_locale_specific: true,
    available_locales: ['de_DE'],
  },
  {
    code: 'nothing',
    type: 'pim_catalog_nothing',
    labels: {},
    scopable: false,
    localizable: false,
  },
];

const channels = [
  {
    code: 'ecommerce',
    locales: [
      {code: 'en_US', label: 'English (United States)', region: 'US', language: 'en'},
      {code: 'fr_FR', label: 'French (France)', region: 'FR', language: 'fr'},
    ],
    labels: {fr_FR: 'Ecommerce'},
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
  {
    code: 'mobile',
    locales: [
      {code: 'de_DE', label: 'German (Germany)', region: 'DE', language: 'de'},
      {code: 'en_US', label: 'English (United States)', region: 'US', language: 'en'},
    ],
    labels: {fr_FR: 'Mobile'},
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
  {
    code: 'print',
    locales: [
      {code: 'de_DE', label: 'German (Germany)', region: 'DE', language: 'de'},
      {code: 'en_US', label: 'English (United States)', region: 'US', language: 'en'},
      {code: 'fr_FR', label: 'French (France)', region: 'FR', language: 'fr'},
    ],
    labels: {fr_FR: 'Impression'},
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
  attribute: {
    fetchByIdentifiers: (identifiers: string[]): Promise<Attribute[]> =>
      Promise.resolve<Attribute[]>(attributes.filter(({code}) => identifiers.includes(code))),
  },
  channel: {fetchAll: (): Promise<Channel[]> => Promise.resolve<Channel[]>(channels)},
};

const renderWithProviders = async (node: ReactNode) =>
  await act(async () => void baseRender(<FetcherContext.Provider value={fetchers}>{node}</FetcherContext.Provider>));

test('it displays source configurator', async () => {
  const source: Source = {
    uuid: 'cffd560e-1e40-4c55-a415-89c7958b270d',
    code: 'description',
    type: 'attribute',
    locale: null,
    channel: null,
    operations: [],
    selection: {
      type: 'code',
    },
  };

  await renderWithProviders(
    <AttributeSourceConfigurator source={source} validationErrors={[]} onSourceChange={jest.fn} />
  );

  expect(
    screen.getByText(/akeneo.tailored_export.column_details.sources.no_source_configuration.title/i)
  ).toBeInTheDocument();
});

test('it displays locale dropdown when attribute is localizable', async () => {
  const source: Source = {
    uuid: 'cffd560e-1e40-4c55-a415-89c7958b270d',
    code: 'description',
    type: 'attribute',
    locale: 'fr_FR',
    channel: null,
    operations: [],
    selection: {
      type: 'code',
    },
  };

  await renderWithProviders(
    <AttributeSourceConfigurator source={source} validationErrors={[]} onSourceChange={jest.fn} />
  );

  expect(screen.getByLabelText(/pim_common.locale/i)).toBeInTheDocument();
  expect(screen.queryByLabelText(/pim_common.channel/i)).not.toBeInTheDocument();
});

test('it displays a filtered locale dropdown when attribute is localizable and locale specific', async () => {
  const source: Source = {
    uuid: 'cffd560e-1e40-4c55-a415-89c7958b270d',
    code: 'locale_specific',
    type: 'attribute',
    locale: 'de_DE',
    channel: null,
    operations: [],
    selection: {
      type: 'code',
    },
  };

  await renderWithProviders(
    <AttributeSourceConfigurator source={source} onSourceChange={jest.fn} validationErrors={[]} />
  );

  userEvent.click(screen.getByLabelText(/pim_common.locale/i));

  expect(screen.getAllByTitle('German (Germany)').length).toEqual(2);
  expect(screen.queryByTitle('English (United States)')).not.toBeInTheDocument();
});

test('it displays a channel dropdown when attribute is scopable', async () => {
  const source: Source = {
    uuid: 'cffd560e-1e40-4c55-a415-89c7958b270d',
    code: 'description',
    type: 'attribute',
    locale: null,
    channel: 'ecommerce',
    operations: [],
    selection: {
      type: 'code',
    },
  };

  await renderWithProviders(
    <AttributeSourceConfigurator source={source} validationErrors={[]} onSourceChange={jest.fn} />
  );

  expect(screen.queryByLabelText(/pim_common.locale/i)).not.toBeInTheDocument();
  expect(screen.getByLabelText(/pim_common.channel/i)).toBeInTheDocument();
});

test('it displays a channel dropdown when attribute is scopable and localizable', async () => {
  const source: Source = {
    uuid: 'cffd560e-1e40-4c55-a415-89c7958b270d',
    code: 'description',
    type: 'attribute',
    locale: 'fr_FR',
    channel: 'ecommerce',
    operations: [],
    selection: {
      type: 'code',
    },
  };

  await renderWithProviders(
    <AttributeSourceConfigurator source={source} validationErrors={[]} onSourceChange={jest.fn} />
  );

  expect(screen.getByLabelText(/pim_common.locale/i)).toBeInTheDocument();
  expect(screen.getByLabelText(/pim_common.channel/i)).toBeInTheDocument();
});

test('it calls handler when channel is changed', async () => {
  const handleSourceChange = jest.fn();
  const source: Source = {
    uuid: 'cffd560e-1e40-4c55-a415-89c7958b270d',
    code: 'description',
    type: 'attribute',
    locale: 'fr_FR',
    channel: 'ecommerce',
    operations: [],
    selection: {
      type: 'code',
    },
  };

  await renderWithProviders(
    <AttributeSourceConfigurator source={source} validationErrors={[]} onSourceChange={handleSourceChange} />
  );

  userEvent.click(screen.getByLabelText(/pim_common.channel/i));
  userEvent.click(screen.getByText('[mobile]'));

  expect(handleSourceChange).toHaveBeenCalledWith({...source, locale: 'de_DE', channel: 'mobile'});
});

test('it calls handler when locale is changed', async () => {
  const handleSourceChange = jest.fn();
  const source: Source = {
    uuid: 'cffd560e-1e40-4c55-a415-89c7958b270d',
    code: 'description',
    type: 'attribute',
    locale: 'fr_FR',
    channel: 'ecommerce',
    operations: [],
    selection: {
      type: 'code',
    },
  };

  await renderWithProviders(
    <AttributeSourceConfigurator source={source} validationErrors={[]} onSourceChange={handleSourceChange} />
  );

  userEvent.click(screen.getByLabelText(/pim_common.locale/i));
  userEvent.click(screen.getByText('English (United States)'));

  expect(handleSourceChange).toHaveBeenCalledWith({...source, locale: 'en_US'});
});

test('it renders nothing if the configurator is unknown', async () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const handleSourceChange = jest.fn();

  await renderWithProviders(
    <AttributeSourceConfigurator
      source={{
        code: 'nothing',
        uuid: 'unique_id',
        type: 'attribute',
      }}
      validationErrors={[]}
      onSourceChange={handleSourceChange}
    />
  );

  expect(mockedConsole).toHaveBeenCalledWith('No configurator found for "pim_catalog_nothing" attribute type');
  mockedConsole.mockRestore();
});
