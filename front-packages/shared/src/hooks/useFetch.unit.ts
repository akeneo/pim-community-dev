import {act} from 'react-test-renderer';
import {useFetch} from './useFetch';
import {renderHookWithProviders} from '../tests';

declare global {
  namespace NodeJS {
    interface Global {
      fetch: any;
    }
  }
}

describe('useFetch', () => {
  const renderUseFetchData = (url: string, init?: RequestInit) => {
    return renderHookWithProviders(() => useFetch(url, init));
  };

  beforeAll(() => {
    global.fetch = jest.fn();
  });

  beforeEach(() => {
    jest.clearAllMocks();
    jest.resetAllMocks();
  });

  afterAll(() => {
    jest.restoreAllMocks();
  });

  test('it returns default values', () => {
    const {result} = renderUseFetchData('/fetch/data');
    const [data, fetch, status, error] = result.current;
    expect(data).toBeNull();
    expect(status).toBe('idle');
    expect(fetch).toBeDefined();
    expect(error).toBeNull();
  });

  test('it loads the data', async () => {
    const response = {foo: 'bar'};

    // @ts-ignore;
    jest.spyOn(global, 'fetch').mockResolvedValue({
      json: () => Promise.resolve(response),
    });

    const {result} = renderUseFetchData('/fetch/data');

    await act(async () => {
      result.current[1]();
    });

    expect(result.current[0]).toEqual({foo: 'bar'});
    expect(result.current[2]).toBe('fetched');
  });

  test('it loads the data with fetching options', async () => {
    const fetchingOptions: RequestInit = {
      method: 'POST',
      headers: new Headers({
        'X-Foo-Bar': 'FooBar',
      }),
    };
    const response = {foo: 'bar'};

    // @ts-ignore;
    const fetcher = jest.spyOn(global, 'fetch').mockResolvedValue({
      json: () => Promise.resolve(response),
    });

    const {result} = renderUseFetchData('/fetch/data', fetchingOptions);

    await act(async () => {
      result.current[1]();
    });

    expect(fetcher).toBeCalledWith('/fetch/data', fetchingOptions);
    expect(result.current[0]).toEqual({foo: 'bar'});
    expect(result.current[2]).toBe('fetched');
  });

  test('it returns errors when the loading failed', async () => {
    // @ts-ignore
    jest.spyOn(global, 'fetch').mockRejectedValue(new Error('An unexpected server error'));

    const {result} = renderUseFetchData('/fetch/bad-data');

    await act(async () => {
      result.current[1]();
    });

    expect(result.current[0]).toBeNull();
    expect(result.current[2]).toEqual('error');
    expect(result.current[3]).toMatch(/unexpected server error/);
  });
});
