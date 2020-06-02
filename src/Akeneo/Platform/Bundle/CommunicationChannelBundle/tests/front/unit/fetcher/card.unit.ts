import {CardFetcherImplementation} from 'akeneocommunicationchannel/fetcher/card';

afterEach(() => {
  global.fetch && global.fetch.mockClear();
  delete global.fetch;
});

test('It can fetch all the cards from the json', async () => {
  const mockJsonPromise = Promise.resolve({});
  const mockFetchPromise = Promise.resolve({
    json: () => mockJsonPromise,
  });
  const fetchMock = jest.fn().mockImplementation(() => mockFetchPromise);
  global.fetch = fetchMock;

  await CardFetcherImplementation.fetchAll();

  expect(global.fetch).toHaveBeenCalledTimes(1);
  expect(global.fetch).toHaveBeenCalledWith(
    './bundles/akeneocommunicationchannel/fetcher/__mocks__/serenity-updates-sample.json'
  );
});
