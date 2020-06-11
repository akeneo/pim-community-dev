import '@testing-library/jest-dom/extend-expect';
import {useAnnouncements} from '@akeneo-pim-community/communication-channel/src/hooks/useAnnouncements';
import {GlobalWithFetchMock} from 'jest-fetch-mock';
import {renderHookWithProviders, fetchMockResponseOnce, getExpectedAnnouncements} from '../../../test-utils';

const customGlobal: GlobalWithFetchMock = global as GlobalWithFetchMock;
customGlobal.fetch = require('jest-fetch-mock');
customGlobal.fetchMock = customGlobal.fetch;

const expectedAnnouncements = getExpectedAnnouncements();

afterEach(() => {
  fetchMock.resetMocks();
});

test('It can get all the announcements', async () => {
  fetchMockResponseOnce(
    './bundles/akeneocommunicationchannel/__mocks__/serenity-updates.json',
    JSON.stringify({data: expectedAnnouncements})
  );

  const {result, waitForNextUpdate} = renderHookWithProviders(useAnnouncements);

  expect(result.current.data).toEqual([]);

  await waitForNextUpdate();

  expect(result.current.data).toEqual(expectedAnnouncements);
  expect(result.current.hasError).toEqual(false);
  expect(fetchMock).toHaveBeenCalledWith('./bundles/akeneocommunicationchannel/__mocks__/serenity-updates.json');
});

test('It can validate the announcements from the json', async () => {
  fetchMockResponseOnce(
    './bundles/akeneocommunicationchannel/__mocks__/serenity-updates.json',
    JSON.stringify({data: [{invalidProperty: 'invalid_property'}]})
  );
  console.error = jest.fn();

  const {result, waitForNextUpdate} = renderHookWithProviders(useAnnouncements);

  await waitForNextUpdate();

  expect(result.current.data).toEqual([]);
  expect(result.current.hasError).toEqual(true);
  expect(console.error).toHaveBeenCalledTimes(1);
});
