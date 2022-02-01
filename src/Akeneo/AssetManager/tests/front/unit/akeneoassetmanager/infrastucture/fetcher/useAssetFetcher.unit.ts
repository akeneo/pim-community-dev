import {useAssetFetcher} from 'akeneoassetmanager/infrastructure/fetcher/useAssetFetcher';
import {renderHook} from '@testing-library/react-hooks';
import {FakeConfigProvider} from '../../utils/FakeConfigProvider';

const {getRequestContract} = require('../../../../acceptance/cucumber/tools');

it('It fetches one asset', async () => {
  global.fetch = jest
    .fn()
    .mockImplementationOnce(() =>
      Promise.resolve({
        json: () => Promise.resolve(getRequestContract('Asset/AssetDetails/ok.json').response.body),
        status: 200,
      })
    )
    .mockImplementationOnce(() =>
      Promise.resolve({
        json: () => Promise.resolve(getRequestContract('AssetFamily/AssetFamilyDetails/ok.json').response.body),
        status: 200,
      })
    );

  const {result} = renderHook(() => useAssetFetcher(), {wrapper: FakeConfigProvider});
  const fetcher = result.current;
  const response = await fetcher.fetch('designer', 'starck');

  expect(response).toMatchObject({
    permission: {
      edit: true,
      assetFamilyIdentifier: 'designer',
    },
    asset: {
      code: 'starck',
      identifier: 'designer_starck_a1677570-a278-444b-ab46-baa1db199392',
      labels: {
        fr_FR: 'Philippe Starck',
      },
      assetFamily: {
        assetCount: 123,
        attributeAsLabel: 'designer_name_123456',
        attributeAsMainMedia: 'designer_portrait_123456',
        code: 'designer',
        identifier: 'designer',
        image: {
          filePath: '5/6/a/5/56a5955ca1fbdf74d8d18ca6e5f62bc74b867a5d_designer.jpg',
          originalFilename: 'designer.jpg',
        },
        labels: {
          en_US: 'Designer',
          fr_FR: 'Concepteur',
        },
      },
    },
  });
});

it('It search for assets', async () => {
  global.fetch = jest.fn().mockImplementationOnce(() =>
    Promise.resolve({
      json: () => Promise.resolve(getRequestContract('Asset/Search/ok.json').response.body),
      status: 200,
    })
  );

  const {result} = renderHook(() => useAssetFetcher(), {wrapper: FakeConfigProvider});
  const fetcher = result.current;
  const response = await fetcher.search({
    locale: 'en_US',
    channel: 'ecommerce',
    size: 200,
    page: 0,
    filters: [
      {
        field: 'full_text',
        operator: '=',
        value: 's',
        context: {},
      },
      {
        field: 'asset_family',
        operator: '=',
        value: 'designer',
        context: {},
      },
    ],
  });

  expect(response).toEqual({
    items: [
      {
        code: 'dyson',
        identifier: 'designer_dyson_01afdc3e-3ecf-4a86-85ef-e81b2d6e95fd',
        labels: {en_US: 'Dyson', fr_FR: 'Dyson'},
        assetFamilyIdentifier: 'designer',
        image: [],
        values: {
          label_designer_d00de54460082b239164135175588647_en_US: {
            attribute: 'label_designer_d00de54460082b239164135175588647',
            channel: null,
            data: 'Dyson',
            locale: 'en_US',
          },
          label_designer_d00de54460082b239164135175588647_fr_FR: {
            attribute: 'label_designer_d00de54460082b239164135175588647',
            channel: null,
            data: 'Dyson',
            locale: 'fr_FR',
          },
          colors_designer_52609e00b7ee307e79eb100099b9a8bf: {
            attribute: 'colors_designer_52609e00b7ee307e79eb100099b9a8bf',
            channel: null,
            data: 'red',
            locale: null,
          },
        },
        completeness: {
          complete: 0,
          required: 1,
        },
      },
      {
        code: 'starck',
        identifier: 'designer_starck_29aea250-bc94-49b2-8259-bbc116410eb2',
        labels: {en_US: 'Starck'},
        assetFamilyIdentifier: 'designer',
        image: [],
        values: {
          'description_designer_29aea250-bc94-49b2-8259-bbc116410eb2_ecommerce_en_US': {
            attribute: 'description_designer_29aea250-bc94-49b2-8259-bbc116410eb2',
            channel: 'ecommerce',
            data: 'an awesome designer!',
            locale: 'en_US',
          },
          label_designer_d00de54460082b239164135175588647_en_US: {
            attribute: 'label_designer_d00de54460082b239164135175588647',
            channel: null,
            data: 'Starck',
            locale: 'en_US',
          },
          colors_designer_52609e00b7ee307e79eb100099b9a8bf: {
            attribute: 'colors_designer_52609e00b7ee307e79eb100099b9a8bf',
            channel: null,
            data: 'red',
            locale: null,
          },
        },
        completeness: {
          complete: 0,
          required: 1,
        },
      },
    ],
    matchesCount: 2,
    totalCount: 3,
  });
});

it('It search for empty assets', async () => {
  global.fetch = jest.fn().mockImplementationOnce(() =>
    Promise.resolve({
      json: () => Promise.resolve(getRequestContract('Asset/Search/no_result.json').response.body),
      status: 200,
    })
  );

  const {result} = renderHook(() => useAssetFetcher(), {wrapper: FakeConfigProvider});
  const fetcher = result.current;
  const response = await fetcher.search({
    locale: 'en_US',
    channel: 'ecommerce',
    size: 200,
    page: 0,
    filters: [
      {
        field: 'full_text',
        operator: '=',
        value: 'search',
        context: {},
      },
      {
        field: 'asset_family',
        operator: '=',
        value: 'designer',
        context: {},
      },
    ],
  });

  expect(response).toEqual({
    items: [],
    matchesCount: 0,
    totalCount: 3,
  });
});
