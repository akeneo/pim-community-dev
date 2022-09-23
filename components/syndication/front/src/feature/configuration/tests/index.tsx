import React, {ReactNode} from 'react';
import {act} from '@testing-library/react';
import {renderWithProviders as baseRender, Channel} from '@akeneo-pim-community/shared';
import {AssetFamily, AssociationType, Attribute, MeasurementFamily} from '../models';
import {FetcherContext} from '../contexts';
import {renderHook, RenderHookResult} from '@testing-library/react-hooks';

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
  {
    code: 'PACK',
    labels: {},
    is_quantified: true,
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
        code: 'meter',
        labels: {
          en_US: 'Meter',
          fr_FR: 'Metre',
        },
      },
    ],
  },
];

const assetFamilies: AssetFamily[] = [
  {
    identifier: 'wallpapers',
    attribute_as_main_media: 'media_blablabla',
    attributes: [
      {
        identifier: 'media_blablabla',
        type: 'media_file',
        value_per_locale: false,
        value_per_channel: false,
      },
    ],
  },
  {
    identifier: 'pokemons',
    attribute_as_main_media: 'link_blablabla',
    attributes: [
      {
        identifier: 'link_blablabla',
        type: 'media_link',
        value_per_locale: false,
        value_per_channel: false,
      },
    ],
  },
  {
    identifier: 'raccoons',
    attribute_as_main_media: 'link_blablabla',
    attributes: [
      {
        identifier: 'link_blablabla',
        type: 'media_link',
        value_per_locale: true,
        value_per_channel: true,
      },
    ],
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
  measurementFamily: {
    fetchByCode: (measurementFamilyCode: string): Promise<MeasurementFamily | undefined> =>
      Promise.resolve(measurementFamilies.find(({code}) => code === measurementFamilyCode)),
    fetchAll: (): Promise<MeasurementFamily[]> => Promise.resolve(measurementFamilies),
  },
  assetFamily: {
    fetchByIdentifier: (assetFamilyIdentifier: string): Promise<AssetFamily | undefined> =>
      Promise.resolve(assetFamilies.find(({identifier}) => identifier === assetFamilyIdentifier)),
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

export {renderWithProviders, renderHookWithProviders};
