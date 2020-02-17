'use strict';

import remover from 'akeneoassetmanager/infrastructure/remover/attribute';
import * as fetch from 'akeneoassetmanager/tools/fetch';

jest.mock('pim/router', () => {});
jest.mock('pim/security-context', () => {}, {virtual: true});
jest.mock('routing');

describe('akeneoassetmanager/infrastructure/remover/attribute', () => {
  it('It deletes an attribute', async () => {
    // @ts-ignore
    fetch.deleteJSON = jest.fn().mockImplementationOnce(() => Promise.resolve());

    await remover.remove('designer', 'name_1234');

    expect(fetch.deleteJSON).toHaveBeenCalledWith('akeneo_asset_manager_attribute_delete_rest');
  });
});
