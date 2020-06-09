import {CardFetcherImplementation} from '@akeneo-pim-community/communication-channel/src/fetcher/card';
import {getExpectedCards} from '../../../test-utils';

afterEach(() => {
  global.fetch && global.fetch.mockClear();
  delete global.fetch;
});

test('It can fetch cards from the json', async () => {
  const mockJsonPromise = Promise.resolve({data: getExpectedCards()});
  const mockFetchPromise = Promise.resolve({
    json: () => mockJsonPromise,
  });
  const fetchMock = jest.fn().mockImplementation(() => mockFetchPromise);
  global.fetch = fetchMock;

  await CardFetcherImplementation.fetchAll();

  expect(global.fetch).toHaveBeenCalledTimes(1);
  expect(global.fetch).toHaveBeenCalledWith(
    './bundles/akeneocommunicationchannel/__mocks__/serenity-updates-sample.json'
  );
});

test('It can validate the cards from the json', async () => {
  const mockJsonPromise = Promise.resolve({data: [{invalidProperty: 'invalid_property'}]});
  const mockFetchPromise = Promise.resolve({
    json: () => mockJsonPromise,
  });
  const fetchMock = jest.fn().mockImplementation(() => mockFetchPromise);
  global.fetch = fetchMock;
  console.error = jest.fn();

  await expect(CardFetcherImplementation.fetchAll()).rejects.toThrowError(Error);
  expect(console.error).toHaveBeenCalledTimes(1);
});
