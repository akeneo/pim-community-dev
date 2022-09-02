'use strict';

import remover from 'akeneoassetmanager/infrastructure/remover/attribute';

describe('akeneoassetmanager/infrastructure/remover/attribute', () => {
  it('It deletes an attribute', async () => {
    global.fetch = jest.fn().mockImplementationOnce(() =>
      Promise.resolve({
        json: () => Promise.resolve({}),
        status: 200,
      })
    );

    await remover.remove('designer', 'name_1234');

    expect(global.fetch).toHaveBeenCalledWith('/rest/asset_manager/designer/attribute/name_1234', {
      method: 'DELETE',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      },
    });
  });
});
