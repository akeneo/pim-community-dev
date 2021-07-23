import React, {ReactNode} from 'react';
import {act} from '@testing-library/react';
import {renderWithProviders as baseRender, Channel} from '@akeneo-pim-community/shared';
import {AssociationType, Attribute} from '../models';
import {FetcherContext} from '../contexts';

const associationTypes: AssociationType[] = [
  {
    code: 'XSELL',
    labels: {en_US: 'Cross sell'},
    is_quantified: false,
  },
  {
    code: 'UPSELL',
    labels: {},
    is_quantified: false,
  },
];

const attributes: Attribute[] = [
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
    is_locale_specific: false,
    available_locales: [],
  },
  {
    type: 'pim_catalog_text',
    code: 'name',
    labels: {fr_FR: 'French name', en_US: 'English name'},
    scopable: false,
    localizable: false,
    is_locale_specific: false,
    available_locales: [],
  },
  {
    type: 'pim_catalog_textarea',
    code: 'description',
    labels: {fr_FR: 'French description', en_US: 'English description'},
    scopable: false,
    localizable: false,
    is_locale_specific: false,
    available_locales: [],
  },
];

const channels: Channel[] = [
  {
    code: 'ecommerce',
    labels: {fr_FR: 'Ecommerce'},
    locales: [
      {
        code: 'en_US',
        label: 'English (United States)',
        region: 'US',
        language: 'en',
      },
      {
        code: 'fr_FR',
        label: 'Français',
        region: 'FR',
        language: 'fr',
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
  {
    code: 'print',
    labels: {},
    locales: [
      {
        code: 'en_US',
        label: 'en_US',
        region: 'US',
        language: 'en',
      },
      {
        code: 'fr_FR',
        label: 'fr_FR',
        region: 'FR',
        language: 'fr',
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
];

const fetchers = {
  attribute: {
    fetchByIdentifiers: (identifiers: string[]): Promise<Attribute[]> =>
      Promise.resolve(attributes.filter(({code}) => identifiers.includes(code))),
  },
  channel: {fetchAll: (): Promise<Channel[]> => Promise.resolve(channels)},
  associationType: {
    fetchByCodes: (codes: string[]): Promise<AssociationType[]> =>
      Promise.resolve(associationTypes.filter(({code}) => codes.includes(code))),
  },
};

const renderWithProviders = async (node: ReactNode) =>
  await act(async () => void baseRender(<FetcherContext.Provider value={fetchers}>{node}</FetcherContext.Provider>));

export {renderWithProviders};
