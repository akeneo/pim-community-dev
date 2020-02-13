'use strict';

const ReferenceEntityBuilder = require('../../../../common/builder/reference-entity.js');

import fetcher from 'akeneoreferenceentity/infrastructure/fetcher/reference-entity';
import {createIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import * as fetch from 'akeneoreferenceentity/tools/fetch';

jest.mock('pim/router', () => {});
jest.mock('pim/security-context', () => {}, {virtual: true});
jest.mock('routing');

describe('Akeneoreferenceentity > infrastructure > fetcher > reference-entity', () => {
  it('It search for reference entities', async () => {
    // @ts-ignore
    fetch.getJSON = jest.fn().mockImplementationOnce(() =>
      Promise.resolve({
        items: [],
      })
    );

    const response = await fetcher.search();

    expect(response).toEqual({
      items: [],
    });
  });

  it('It fetches one reference entity', async () => {
    const referenceEntity = new ReferenceEntityBuilder()
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
      .withAttributeAsImage('')
      .withAttributeAsLabel('')
      .build();

    // @ts-ignore
    fetch.getJSON = jest.fn().mockImplementationOnce(() => Promise.resolve(referenceEntity));

    const referenceEntityIdentifier = createIdentifier('sofa');
    const response = await fetcher.fetch(referenceEntityIdentifier);

    expect(response).toEqual({
      attributes: [],
      recordCount: 123,
      referenceEntity: {
        attributeAsImage: {
          identifier: '',
        },
        attributeAsLabel: {
          identifier: '',
        },
        identifier: {
          identifier: 'sofa',
        },
        labelCollection: {
          labels: {
            en_US: 'Sofa',
            fr_FR: 'Canapé',
          },
        },
        image: {
          filePath: '/path/sofa.jpg',
          originalFilename: 'sofa.jpg',
        },
      },
      permission: {edit: true, referenceEntityIdentifier: 'sofa'},
    });
  });
});
