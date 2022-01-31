'use strict';

import remover from 'akeneoassetmanager/infrastructure/remover/asset';
import * as fetch from 'akeneoassetmanager/tools/fetch';

jest.mock('routing', () => ({
  generate: (url: string) => url,
}));

describe('akeneoassetmanager/infrastructure/remover/asset', () => {
  it('It deletes a asset', async () => {
    // @ts-ignore
    fetch.deleteJSON = jest.fn().mockImplementationOnce(() => Promise.resolve());

    await remover.remove('designer', 'starck');

    expect(fetch.deleteJSON).toHaveBeenCalledWith('akeneo_asset_manager_asset_delete_rest');
  });

  it('It deletes all asset family assets', async () => {
    // @ts-ignore
    fetch.deleteJSON = jest.fn().mockImplementationOnce(() => Promise.resolve());

    await remover.removeAll('designer');

    expect(fetch.deleteJSON).toHaveBeenCalledWith('akeneo_asset_manager_asset_delete_all_rest');
  });
});
