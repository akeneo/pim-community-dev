import React, {ReactNode} from 'react';
import {act} from '@testing-library/react';
import {renderHook, RenderHookResult} from '@testing-library/react-hooks';
import {renderWithProviders as baseRender, Channel} from '@akeneo-pim-community/shared';
import {Attribute, MeasurementFamily} from '../models';
import {FetcherContext} from '../contexts';

const attributes: Attribute[] = [
  {
    code: 'sku',
    type: 'pim_catalog_identifier',
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
    localizable: true,
    is_locale_specific: true,
    available_locales: ['br_FR'],
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
    type: 'pim_catalog_metric',
    code: 'weight',
    labels: {fr_FR: 'Poids', en_US: 'Weight'},
    scopable: false,
    localizable: false,
    is_locale_specific: false,
    available_locales: [],
  },
  {
    type: 'pim_catalog_textarea',
    code: 'description',
    labels: {fr_FR: 'French description', en_US: 'English description'},
    scopable: true,
    localizable: true,
    is_locale_specific: false,
    available_locales: [],
  },
  {
    type: 'pim_catalog_identifier',
    code: 'sku',
    labels: {fr_FR: 'Scul', en_US: 'Sku'},
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
    currencies: ['EUR', 'USD'],
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
    currencies: ['USD', 'DKK'],
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

const measurementFamilies: MeasurementFamily[] = [
  {
    code: 'Weight',
    units: [
      {
        code: 'gram',
        labels: {
          en_US: 'Gram',
          fr_FR: 'Gramme',
        },
      },
      {
        code: 'kilogram',
        labels: {
          en_US: 'Kilogram',
          fr_FR: 'Kilogramme',
        },
      },
    ],
  },
];

const fetchers = {
  attribute: {
    fetchByIdentifiers: (identifiers: string[]): Promise<Attribute[]> =>
      Promise.resolve(attributes.filter(({code}) => identifiers.includes(code))),
    fetchAttributeIdentifier: (): Promise<Attribute> => Promise.resolve(attributes[0]),
  },
  channel: {fetchAll: (): Promise<Channel[]> => Promise.resolve(channels)},
  measurementFamily: {
    fetchByCode: (measurementFamilyCode: string): Promise<MeasurementFamily | undefined> =>
      Promise.resolve(measurementFamilies.find(({code}) => code === measurementFamilyCode)),
  },
};

type WrapperProps = {
  children?: ReactNode;
};

const Wrapper = ({children}: WrapperProps) => (
  <FetcherContext.Provider value={fetchers}>{children}</FetcherContext.Provider>
);

const renderWithProviders = async (children: ReactNode) =>
  await act(async () => void baseRender(<Wrapper>{children}</Wrapper>));

const renderHookWithProviders = <P, R>(hook: (props: P) => R, initialProps?: P): RenderHookResult<P, R> =>
  renderHook(hook, {wrapper: Wrapper, initialProps});

export {renderWithProviders, renderHookWithProviders, channels};
