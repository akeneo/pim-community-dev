/* eslint-disable jest/no-conditional-expect */

import {apiFetch, BadRequestError, ForbiddenError} from './apiFetch';

afterEach(() => {
  jest.restoreAllMocks();
});

test('GET request with 200 response', async () => {
  const jestSpy = jest.spyOn(window, 'fetch').mockResolvedValueOnce(new Response('{"data":"some_data"}'));

  const result = await apiFetch('some_url', {method: 'GET', headers: {'X-Custom-Header': 'some_header'}});

  expect(result).toEqual({data: 'some_data'});

  expect(jestSpy).toHaveBeenCalledWith('some_url', {
    method: 'GET',
    headers: {
      'X-Custom-Header': 'some_header',
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
  });
});

test('GET request with 400 response', async () => {
  const jestSpy = jest
    .spyOn(window, 'fetch')
    .mockResolvedValueOnce(new Response('{"error":"some_error"}', {status: 400}));

  try {
    await apiFetch('some_url', {method: 'GET'});
  } catch (error) {
    expect(error).toBeInstanceOf(BadRequestError);
    if (error instanceof BadRequestError) {
      expect(error.data).toEqual({error: 'some_error'});
    }
  }

  expect(jestSpy).toHaveBeenCalledWith('some_url', {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
  });
});

test('GET request with 422 response', async () => {
  const jestSpy = jest
    .spyOn(window, 'fetch')
    .mockResolvedValueOnce(new Response('{"error":"some_error"}', {status: 422}));

  try {
    await apiFetch('some_url', {method: 'GET'});
  } catch (error) {
    expect(error).toBeInstanceOf(BadRequestError);
    if (error instanceof BadRequestError) {
      expect(error.data).toEqual({error: 'some_error'});
    }
  }

  expect(jestSpy).toHaveBeenCalledWith('some_url', {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
  });
});

test('GET request with 403 response', async () => {
  const jestSpy = jest.spyOn(window, 'fetch').mockResolvedValueOnce(new Response('{}', {status: 403}));

  try {
    await apiFetch('some_url', {method: 'GET'});
  } catch (error) {
    expect(error).toBeInstanceOf(ForbiddenError);
  }

  expect(jestSpy).toHaveBeenCalledWith('some_url', {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
  });
});

test('GET request with 500 response', async () => {
  const jestSpy = jest.spyOn(window, 'fetch').mockResolvedValueOnce(new Response('{}', {status: 500}));

  try {
    await apiFetch('some_url', {method: 'GET'});
  } catch (error) {
    expect(error).toBeInstanceOf(Error);
  }

  expect(jestSpy).toHaveBeenCalledWith('some_url', {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
  });
});
