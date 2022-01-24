'use strict';

import namingConventionExecutor from 'akeneoassetmanager/infrastructure/naming-convention-executor';

describe('It executes naming convention for one asset', () => {
  it('It successfully executes a naming convention', async () => {
    const assetCode = 'user_manual.pdf';
    const assetFamilyIdentifier = 'notice';
    global.fetch = jest.fn().mockImplementationOnce(() =>
      Promise.resolve({
        json: () => Promise.resolve({}),
        status: 200,
      })
    );

    await namingConventionExecutor.execute(assetFamilyIdentifier, assetCode);

    expect(global.fetch).toBeCalledWith('/rest/asset_manager/notice/asset/user_manual.pdf/execute_naming_convention', {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      },
    });
  });

  it('It fails to execute a naming convention', async () => {
    const assetCode = 'user_manual.pdf';
    const assetFamilyIdentifier = 'notice';
    global.fetch = jest.fn().mockImplementationOnce(() =>
      Promise.reject({
        status: 500,
      })
    );

    await expect(namingConventionExecutor.execute(assetFamilyIdentifier, assetCode)).rejects.not.toThrow();
    expect(global.fetch).toBeCalledWith('/rest/asset_manager/notice/asset/user_manual.pdf/execute_naming_convention', {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      },
    });
  });
});

describe('It executes naming convention for all assets', () => {
  it('It successfully executes the naming convention for all assets', async () => {
    const assetFamilyIdentifier = 'notice';
    global.fetch = jest.fn().mockImplementationOnce(() =>
      Promise.resolve({
        json: () => Promise.resolve({}),
        status: 200,
      })
    );

    await namingConventionExecutor.executeAll(assetFamilyIdentifier);

    expect(global.fetch).toBeCalledWith('/rest/asset_manager/notice/execute_naming_convention', {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      },
    });
  });

  it('It fails to execute the naming convention for all assets', async () => {
    const assetFamilyIdentifier = 'notice';
    global.fetch = jest.fn().mockImplementationOnce(() =>
      Promise.reject({
        json: () => Promise.resolve({}),
        status: 500,
      })
    );

    await expect(namingConventionExecutor.executeAll(assetFamilyIdentifier)).rejects.not.toThrow();
    expect(global.fetch).toBeCalledWith('/rest/asset_manager/notice/execute_naming_convention', {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      },
    });
  });
});
