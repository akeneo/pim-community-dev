'use strict';

const AssetFamilyBuilder = require('../../../../common/builder/asset-family.js');

import fetcher from 'akeneoassetmanager/infrastructure/fetcher/asset-family';

describe('akeneoassetmanager/infrastructure/fetcher/asset-family', () => {
  it('It search for asset families', async () => {
    global.fetch = jest.fn().mockImplementationOnce(() =>
      Promise.resolve({
        json: () =>
          Promise.resolve({
            items: [],
          }),
      })
    );

    const response = await fetcher.search();

    expect(response).toEqual({
      items: [],
    });
  });

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

    // @ts-ignore
    global.fetch = jest.fn().mockImplementationOnce(() =>
      Promise.resolve({
        json: () => Promise.resolve(assetFamily),
      })
    );

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
});
