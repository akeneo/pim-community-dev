'use strict';

import remover from 'akeneoassetmanager/infrastructure/remover/asset-family';
import * as fetch from 'akeneoassetmanager/tools/fetch';

jest.mock('pim/router', () => {});
jest.mock('pim/security-context', () => {}, {virtual: true});
jest.mock('routing');

describe('akeneoassetmanager/infrastructure/remover/asset-family', () => {
  it('It deletes an asset family', async () => {
    // @ts-ignore
    fetch.deleteJSON = jest.fn().mockImplementationOnce(() => Promise.resolve());

    await remover.remove('designer');

    expect(fetch.deleteJSON).toHaveBeenCalledWith('akeneo_asset_manager_asset_family_delete_rest');
  });
});
