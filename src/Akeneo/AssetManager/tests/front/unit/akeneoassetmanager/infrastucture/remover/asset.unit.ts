'use strict';

import remover from 'akeneoassetmanager/infrastructure/remover/asset';
import * as fetch from 'akeneoassetmanager/tools/fetch';
import {createQuery} from 'akeneoassetmanager/application/hooks/grid';

jest.mock('pim/router', () => {});
jest.mock('pim/security-context', () => {}, {virtual: true});
jest.mock('routing');

describe('akeneoassetmanager/infrastructure/remover/asset', () => {
  it('It deletes a asset', async () => {
    jest.spyOn(fetch, 'deleteJSON').mockImplementation(() => Promise.resolve());

    await remover.remove('designer', 'starck');

    expect(fetch.deleteJSON).toHaveBeenCalledWith('akeneo_asset_manager_asset_delete_rest');
  });

  it('It mass deletes asset from query', async () => {
    jest.spyOn(global, 'fetch').mockImplementation(() => Promise.resolve(new Response('{}')));

    const query = createQuery(
      'packshot',
      [
        {
          field: 'code',
          value: ['red', 'blue'],
          operator: 'IN',
          context: {},
        },
      ],
      '',
      [],
      'en_US',
      'ecommerce',
      0,
      50
    );

    await remover.removeFromQuery('designer', query);

    expect(global.fetch).toHaveBeenCalledWith('akeneo_asset_manager_asset_mass_delete_rest', {
      body: query,

    });
  });
});
