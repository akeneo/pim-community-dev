'use strict';

import remover from 'akeneoassetmanager/infrastructure/remover/asset';
import {createQuery} from 'akeneoassetmanager/application/hooks/grid';

describe('akeneoassetmanager/infrastructure/remover/asset', () => {
  it('It deletes an asset', async () => {
    global.fetch = jest.fn().mockImplementationOnce(() =>
      Promise.resolve({
        json: () => Promise.resolve({}),
        status: 200,
      })
    );

    await remover.remove('designer', 'starck');

    expect(global.fetch).toHaveBeenCalledWith('/rest/asset_manager/designer/asset/starck', {
      method: 'DELETE',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      },
    });
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

    expect(global.fetch).toHaveBeenCalledWith('/rest/asset_manager/designer/assets', {
      body: JSON.stringify(query),
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });
  });
});
