'use strict';

import namingConventionExecutor from 'akeneoassetmanager/infrastructure/naming-convention-executor';
import * as fetch from 'akeneoassetmanager/tools/fetch';
import handleError from 'akeneoassetmanager/infrastructure/tools/error-handler';

const routing = require('routing');

const EXECUTE_NAMING_CONVENTION_ACTUAL_ROUTE = '/ROUTE/TO/BE/CALLED';
const ASSET_EXECUTE_NAMING_CONVENTION_ROUTE_CODE = 'akeneo_asset_manager_asset_execute_naming_convention';
const ASSET_FAMILY_EXECUTE_NAMING_CONVENTION_ROUTE_CODE = 'akeneo_asset_manager_asset_family_execute_naming_convention';

jest.mock('pim/router', () => {});
jest.mock('pim/security-context', () => {}, {virtual: true});
jest.mock('routing');
jest.mock('akeneoassetmanager/infrastructure/tools/error-handler');

describe('It executes naming convention for one asset', () => {
  it('It successfully executes a naming convention', async () => {
    const assetCode = 'user_manual.pdf';
    const assetFamilyIdentifier = 'notice';
    // @ts-ignore
    routing.generate = jest.fn().mockImplementation(() => EXECUTE_NAMING_CONVENTION_ACTUAL_ROUTE);
    // @ts-ignore
    fetch.postJSON = jest.fn().mockImplementationOnce(() => Promise.resolve({}));

    await namingConventionExecutor.execute(assetFamilyIdentifier, assetCode);

    expect(routing.generate).toBeCalledWith(ASSET_EXECUTE_NAMING_CONVENTION_ROUTE_CODE, {
      assetCode: assetCode,
      assetFamilyIdentifier: assetFamilyIdentifier,
    });
    expect(fetch.postJSON).toBeCalledWith(EXECUTE_NAMING_CONVENTION_ACTUAL_ROUTE, {});
  });

  it('It fails to execute a naming convention', async () => {
    const assetCode = 'user_manual.pdf';
    const assetFamilyIdentifier = 'notice';
    // @ts-ignore
    routing.generate = jest.fn().mockImplementation(() => EXECUTE_NAMING_CONVENTION_ACTUAL_ROUTE);
    // @ts-ignore
    fetch.postJSON = jest.fn().mockImplementationOnce(() => Promise.reject({}));

    await namingConventionExecutor.execute(assetFamilyIdentifier, assetCode);

    expect(routing.generate).toBeCalledWith(ASSET_EXECUTE_NAMING_CONVENTION_ROUTE_CODE, {
      assetCode: assetCode,
      assetFamilyIdentifier: assetFamilyIdentifier,
    });
    expect(fetch.postJSON).toBeCalledWith(EXECUTE_NAMING_CONVENTION_ACTUAL_ROUTE, {});
    expect(handleError).toBeCalled();
  });
});

describe('It executes naming convention for all assets', () => {
  it('It successfully executes the naming convention for all assets', async () => {
    const assetFamilyIdentifier = 'notice';
    // @ts-ignore
    routing.generate = jest.fn().mockImplementation(() => EXECUTE_NAMING_CONVENTION_ACTUAL_ROUTE);
    // @ts-ignore
    fetch.postJSON = jest.fn().mockImplementationOnce(() => Promise.resolve({}));

    await namingConventionExecutor.executeAll(assetFamilyIdentifier);

    expect(routing.generate).toBeCalledWith(ASSET_FAMILY_EXECUTE_NAMING_CONVENTION_ROUTE_CODE, {
      assetFamilyIdentifier: assetFamilyIdentifier,
    });
    expect(fetch.postJSON).toBeCalledWith(EXECUTE_NAMING_CONVENTION_ACTUAL_ROUTE, {});
  });

  it('It fails to execute the naming convention for all assets', async () => {
    const assetFamilyIdentifier = 'notice';
    // @ts-ignore
    routing.generate = jest.fn().mockImplementation(() => EXECUTE_NAMING_CONVENTION_ACTUAL_ROUTE);
    // @ts-ignore
    fetch.postJSON = jest.fn().mockImplementationOnce(() => Promise.reject({}));

    await namingConventionExecutor.executeAll(assetFamilyIdentifier);

    expect(routing.generate).toBeCalledWith(ASSET_FAMILY_EXECUTE_NAMING_CONVENTION_ROUTE_CODE, {
      assetFamilyIdentifier: assetFamilyIdentifier,
    });
    expect(fetch.postJSON).toBeCalledWith(EXECUTE_NAMING_CONVENTION_ACTUAL_ROUTE, {});
    expect(handleError).toBeCalled();
  });
});
