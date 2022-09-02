import {FakeConfigProvider} from '../../utils/FakeConfigProvider';

const AssetFamilyBuilder = require('../../../../common/builder/asset-family.js');
import {useAssetFamilyFetcher} from 'akeneoassetmanager/infrastructure/fetcher/useAssetFamilyFetcher';
import {renderHook} from '@testing-library/react-hooks';

it('It fetches one asset family', async () => {
  const assetFamily = new AssetFamilyBuilder()
    .withIdentifier('sofa')
    .withLabels({
      en_US: 'Sofa',
      fr_FR: 'Canapé',
    })
    .withImage({
      filePath: '/path/sofa.jpg',
      originalFilename: 'sofa.jpg',
    })
    .withAttributes([])
    .withAttributeAsMainMedia('')
    .withAttributeAsLabel('')
    .build();

  global.fetch = jest.fn().mockImplementationOnce(() =>
    Promise.resolve({
      json: () => Promise.resolve(assetFamily),
      status: 200,
    })
  );

  const {result} = renderHook(() => useAssetFamilyFetcher(), {wrapper: FakeConfigProvider});
  const fetcher = result.current;
  const response = await fetcher.fetch('sofa');

  expect(response).toEqual({
    attributes: [],
    assetCount: 123,
    assetFamily: {
      assetCount: 123,
      attributeAsMainMedia: '',
      attributeAsLabel: '',
      attributes: [],
      identifier: 'sofa',
      transformations: [],
      code: 'sofa',
      labels: {
        en_US: 'Sofa',
        fr_FR: 'Canapé',
      },
      image: {
        filePath: '/path/sofa.jpg',
        originalFilename: 'sofa.jpg',
      },
    },
    permission: {
      edit: true,
      assetFamilyIdentifier: 'sofa',
    },
  });
});

it('It fetches all asset families', async () => {
  const mockedResponse = {
    items: [
      {
        identifier: 'atmosphere',
        labels: {en_US: 'Atmosphere'},
        image: null,
      },
      {
        identifier: 'notice',
        labels: {en_US: 'Notice'},
        image: null,
      },
      {
        identifier: 'packshot',
        labels: {en_US: 'Packshot'},
        image: null,
      },
    ],
    total: 4,
  };

  global.fetch = jest.fn().mockImplementationOnce(() =>
    Promise.resolve({
      json: () => Promise.resolve(mockedResponse),
      status: 200,
    })
  );

  const {result} = renderHook(() => useAssetFamilyFetcher(), {wrapper: FakeConfigProvider});
  const fetcher = result.current;
  const response = await fetcher.fetchAll();

  expect(response).toEqual([
    {
      identifier: 'atmosphere',
      labels: {en_US: 'Atmosphere'},
      image: null,
    },
    {
      identifier: 'notice',
      labels: {en_US: 'Notice'},
      image: null,
    },
    {
      identifier: 'packshot',
      labels: {en_US: 'Packshot'},
      image: null,
    },
  ]);
});
