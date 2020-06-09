import {AnnouncementFetcher} from '@akeneo-pim-community/communication-channel/src/fetcher/announcement';
import {getExpectedAnnouncements} from '../../../test-utils';
import {GlobalWithFetchMock} from 'jest-fetch-mock';

const customGlobal: GlobalWithFetchMock = global as GlobalWithFetchMock;
customGlobal.fetch = require('jest-fetch-mock');
customGlobal.fetchMock = customGlobal.fetch;

afterEach(() => {
  fetchMock.resetMocks();
});

test('It can fetch announcements from the json', async () => {
  const mockJsonPromise = JSON.stringify({data: getExpectedAnnouncements()});
  fetchMock.mockResponseOnce(() => Promise.resolve(mockJsonPromise));

  await AnnouncementFetcher.fetchAll();

  expect(customGlobal.fetch).toHaveBeenCalledTimes(1);
  expect(customGlobal.fetch).toHaveBeenCalledWith(
    './bundles/akeneocommunicationchannel/__mocks__/serenity-updates.json'
  );
});

test('It can validate the announcements from the json', async () => {
  const mockJsonPromise = JSON.stringify({data: [{invalidProperty: 'invalid_property'}]});
  fetchMock.mockResponseOnce(() => Promise.resolve(mockJsonPromise));
  console.error = jest.fn();

  await expect(AnnouncementFetcher.fetchAll()).rejects.toThrowError(Error);
  expect(console.error).toHaveBeenCalledTimes(1);
});
