require('jest-fetch-mock').enableMocks();
import fetchMock from 'jest-fetch-mock';
import {getJson, HttpError} from './http';

beforeEach(() => fetchMock.resetMocks());

test('it fetches JSON as an XHR', async () => {
  fetchMock.mockResponseOnce(JSON.stringify({foo: 'bar'}));

  expect(await getJson('/foo')).toEqual({foo: 'bar'});
  expect(fetchMock).toHaveBeenCalledWith('/foo', {
    credentials: 'same-origin',
    headers: {Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
  });
});

test('it can be constructed like a regular error', () => {
  const error = new HttpError('/foo', 500);

  expect(error.message).toBe('Request to "/foo" failed with status 500');
  expect(error).toBeInstanceOf(Error);
});

test('it rejects with the HTTP status', async () => {
  fetchMock.mockResponseOnce('', {status: 403});

  const error = await getJson('/foo').catch(error => error);
  expect(error).toBeInstanceOf(HttpError);
  expect(error.status).toBe(403);
});
