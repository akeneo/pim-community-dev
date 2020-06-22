import {fetchAnnouncements} from '../../../../src/fetcher/announcementFetcher';
import {getExpectedAnnouncements} from '../__mocks__/dataProvider';
import {fetchMockResponseOnce} from '@akeneo-pim-community/shared/tests/front/unit/utils';

afterEach(() => {
  fetchMock.resetMocks();
});

test('it can fetch the first announcement items', async () => {
  const limit = 2;
  const route = `/rest/announcements?limit=${limit}`;
  const expectedAnnouncements = getExpectedAnnouncements();
  fetchMockResponseOnce(route, JSON.stringify({items: expectedAnnouncements}));

  const firstAnnouncements = await fetchAnnouncements(null, limit);

  expect(firstAnnouncements).toStrictEqual(expectedAnnouncements);
  expect(fetch).toBeCalledWith(route);
});

test('it can fetch the announcement items after a given id', async () => {
  const searchAfter = 'test-id';
  const limit = 2;
  const route = `/rest/announcements?limit=${limit}&search_after=${searchAfter}`;
  const expectedAnnouncements = getExpectedAnnouncements();
  fetchMockResponseOnce(route, JSON.stringify({items: expectedAnnouncements}));

  const nextAnnouncements = await fetchAnnouncements(searchAfter, limit);

  expect(nextAnnouncements).toStrictEqual(expectedAnnouncements);
  expect(fetch).toBeCalledWith(route);
});

test('it can validates the anouncement items', async () => {
  const limit = 2;
  console.error = jest.fn();
  fetchMockResponseOnce(
    `/rest/announcements?limit=${limit}`,
    JSON.stringify({items: [{wrong_property: 'wrong_property'}]})
  );

  await expect(fetchAnnouncements(null, limit)).rejects.toThrowError();
  expect(console.error).toHaveBeenCalledTimes(1);
});
