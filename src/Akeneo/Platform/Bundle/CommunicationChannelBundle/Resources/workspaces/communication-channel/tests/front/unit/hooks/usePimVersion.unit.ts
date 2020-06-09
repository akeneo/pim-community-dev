import '@testing-library/jest-dom/extend-expect';
import {usePimVersion} from '@akeneo-pim-community/communication-channel/src/hooks/usePimVersion';
import {GlobalWithFetchMock} from 'jest-fetch-mock';
import {renderHookWithProviders} from '../../../test-utils';

const customGlobal: GlobalWithFetchMock = global as GlobalWithFetchMock;
customGlobal.fetch = require('jest-fetch-mock');
customGlobal.fetchMock = customGlobal.fetch;

afterEach(() => {
  fetchMock.resetMocks();
});

test('It gets the PimVersion from the pim analytics data', async () => {
  const expectedData = {pim_version: '4.0', pim_edition: 'CE', other_properties: 'other_value'};
  const expectedPimVersion = {
    edition: 'CE',
    version: '4.0',
  };
  const mockJsonPromise = JSON.stringify(expectedData);
  fetchMock.mockResponseOnce(() => Promise.resolve(mockJsonPromise));

  const {result, waitForNextUpdate} = renderHookWithProviders(usePimVersion);

  expect(result.current.pimVersion).toEqual(null);

  await waitForNextUpdate();

  expect(result.current.pimVersion).toEqual(expectedPimVersion);
  expect(typeof result.current.updatePimVersion).toBe('function');
  expect(fetchMock).toHaveBeenCalledWith('pim_analytics_data_collect');
});

test('It can validate the pim analytics data', async () => {
  const expectedData = {pim_version: '1384859', pim_edition: true};
  const mockJsonPromise = JSON.stringify(expectedData);
  fetchMock.mockResponseOnce(() => Promise.resolve(mockJsonPromise));
  console.error = jest.fn();

  const {result, waitForNextUpdate} = renderHookWithProviders(usePimVersion);

  await waitForNextUpdate();

  expect(result.current.pimVersion).toEqual(null);
  expect(console.error).toHaveBeenCalledTimes(1);
});
