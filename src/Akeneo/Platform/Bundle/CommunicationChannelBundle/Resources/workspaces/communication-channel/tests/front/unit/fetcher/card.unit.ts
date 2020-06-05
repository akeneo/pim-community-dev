import {CardFetcherImplementation} from '@src/fetcher/card';

console.error = jest.fn();

const expectedCards = [
  {
    title: 'Title card',
    description: 'Description card',
    img: '/path/img/card.png',
    link: 'http://link-card.com',
    tags: ['new'],
    date: '20-04-2020',
  },
  {
    title: 'Title card 2',
    description: 'Description card 2',
    img: '/path/img/card-2.png',
    link: 'http://link-card-2.com',
    tags: ['new'],
    date: '20-04-2020',
  },
];

afterEach(() => {
  global.fetch && global.fetch.mockClear();
  delete global.fetch;
});

test('It can fetch cards from the json', async () => {
  const mockJsonPromise = Promise.resolve({data: expectedCards});
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

test('It can validate the cards from the json', async () => {
  const mockJsonPromise = Promise.resolve({data: [{invalidProperty: 'invalid_property'}]});
  const mockFetchPromise = Promise.resolve({
    json: () => mockJsonPromise,
  });
  const fetchMock = jest.fn().mockImplementation(() => mockFetchPromise);
  global.fetch = fetchMock;

  await expect(CardFetcherImplementation.fetchAll()).rejects.toThrowError(Error);
  expect(console.error).toHaveBeenCalledTimes(1);
});
