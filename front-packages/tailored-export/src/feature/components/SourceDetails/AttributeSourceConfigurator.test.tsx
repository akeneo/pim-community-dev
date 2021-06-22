import React from 'react';
import {screen, act, fireEvent} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders, Channel} from '@akeneo-pim-community/shared';
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
];
const channels = [
  {
    code: 'ecommerce',
    locales: [
      {code: 'en_US', label: 'English (United States)'},
      {code: 'fr_FR', label: 'French (France)'},
    ],
    labels: {fr_FR: 'Ecommerce'},
  },
  {
    code: 'mobile',
    locales: [
      {code: 'de_DE', label: 'German (Germany)'},
      {code: 'en_US', label: 'English (United States)'},
    ],
    labels: {fr_FR: 'Mobile'},
  },
  {
    code: 'print',
    locales: [
      {code: 'de_DE', label: 'German (Germany)'},
      {code: 'en_US', label: 'English (United States)'},
      {code: 'fr_FR', label: 'French (France)'},
    ],
    labels: {fr_FR: 'Impression'},
  },
];

const fetchers = {
  attribute: {
    fetchByIdentifiers: (identifiers: string[]): Promise<Attribute[]> =>
      Promise.resolve<Attribute[]>(attributes.filter(({code}) => identifiers.includes(code))),
  },
  channel: {fetchAll: (): Promise<Channel[]> => Promise.resolve<Channel[]>(channels)},
};

test('it display source configurator', async () => {
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

  await act(async () => {
    renderWithProviders(
      <FetcherContext.Provider value={fetchers}>
        <AttributeSourceConfigurator source={source} validationErrors={[]} onSourceChange={jest.fn} />
      </FetcherContext.Provider>
    );
  });

  expect(
    screen.getByText(/akeneo.tailored_export.column_details.sources.no_source_configuration.title/i)
  ).toBeInTheDocument();
});

test('it display locale dropdown when attribute is localizable', async () => {
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

  await act(async () => {
    renderWithProviders(
      <FetcherContext.Provider value={fetchers}>
        <AttributeSourceConfigurator source={source} validationErrors={[]} onSourceChange={jest.fn} />
      </FetcherContext.Provider>
    );
  });

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

  await act(async () => {
    renderWithProviders(
      <FetcherContext.Provider value={fetchers}>
        <AttributeSourceConfigurator source={source} onSourceChange={jest.fn} validationErrors={[]} />
      </FetcherContext.Provider>
    );
  });

  const localeDropdown = screen.getByLabelText(/pim_common.locale/i);
  userEvent.click(localeDropdown);

  expect(screen.getAllByTitle('German (Germany)').length).toEqual(2);
  expect(screen.queryByTitle('English (United States)')).not.toBeInTheDocument();
});

test('it display channel dropdown when attribute is scopable', async () => {
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

  await act(async () => {
    renderWithProviders(
      <FetcherContext.Provider value={fetchers}>
        <AttributeSourceConfigurator source={source} validationErrors={[]} onSourceChange={jest.fn} />
      </FetcherContext.Provider>
    );
  });

  expect(screen.queryByLabelText(/pim_common.locale/i)).not.toBeInTheDocument();
  expect(screen.getByLabelText(/pim_common.channel/i)).toBeInTheDocument();
});

test('it display channel dropdown when attribute is scopable and localizable', async () => {
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

  await act(async () => {
    renderWithProviders(
      <FetcherContext.Provider value={fetchers}>
        <AttributeSourceConfigurator source={source} validationErrors={[]} onSourceChange={jest.fn} />
      </FetcherContext.Provider>
    );
  });

  expect(screen.getByLabelText(/pim_common.locale/i)).toBeInTheDocument();
  expect(screen.getByLabelText(/pim_common.channel/i)).toBeInTheDocument();
});

test('it calls handler when channel changed', async () => {
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

  await act(async () => {
    renderWithProviders(
      <FetcherContext.Provider value={fetchers}>
        <AttributeSourceConfigurator source={source} validationErrors={[]} onSourceChange={handleSourceChange} />
      </FetcherContext.Provider>
    );
  });

  const channelDropdown = screen.getByLabelText(/pim_common.channel/i);
  userEvent.click(channelDropdown);

  fireEvent.click(screen.getByText('[mobile]'));

  expect(handleSourceChange).toHaveBeenCalledWith({...source, locale: 'de_DE', channel: 'mobile'});
});

test('it calls handler when locale changed', async () => {
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

  await act(async () => {
    renderWithProviders(
      <FetcherContext.Provider value={fetchers}>
        <AttributeSourceConfigurator source={source} validationErrors={[]} onSourceChange={handleSourceChange} />
      </FetcherContext.Provider>
    );
  });

  const localeDropdown = screen.getByLabelText(/pim_common.locale/i);
  userEvent.click(localeDropdown);

  fireEvent.click(screen.getByText('English (United States)'));

  expect(handleSourceChange).toHaveBeenCalledWith({...source, locale: 'en_US'});
});
