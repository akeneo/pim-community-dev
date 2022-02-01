'use strict';

import remover from 'akeneoassetmanager/infrastructure/remover/asset-family';

describe('akeneoassetmanager/infrastructure/remover/asset-family', () => {
  it('It deletes an asset family', async () => {
    global.fetch = jest.fn().mockImplementationOnce(() =>
      Promise.resolve({
        json: () => Promise.resolve({}),
        status: 200,
      })
    );

    await remover.remove('designer');

    expect(global.fetch).toHaveBeenCalledWith('/rest/asset_manager/designer', {
      method: 'DELETE',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      },
    });
  });
});
