import {act} from 'react-test-renderer';
import {renderHookWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {useFetch} from '@akeneo-pim-community/shared';

describe('useFetch', () => {
  const renderUseFetchData = (url: string, init?: RequestInit) => {
    return renderHookWithProviders(() => useFetch(url, init));
  };

  beforeEach(() => {
    jest.clearAllMocks();
    jest.resetAllMocks();
  });

  afterAll(() => {
    jest.restoreAllMocks();
  });

  test('it returns default values', () => {
    const {result} = renderUseFetchData('/fetch/data');
    expect(result.current.data).toBeNull();
    expect(result.current.status).toBe('idle');
    expect(result.current.fetch).toBeDefined();
    expect(result.current.error).toBeNull();
  });

  test('it loads the data', async () => {
    const response = {foo: 'bar'};

    // @ts-ignore;
    jest.spyOn(global, 'fetch').mockResolvedValue({
      json: () => Promise.resolve(response),
    });

    const {result} = renderUseFetchData('/fetch/data');

    await act(async () => {
      result.current.fetch();
    });

    expect(result.current.data).toEqual({foo: 'bar'});
    expect(result.current.status).toBe('fetched');
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
      result.current.fetch();
    });

    expect(fetcher).toBeCalledWith('/fetch/data', fetchingOptions);
    expect(result.current.data).toEqual({foo: 'bar'});
    expect(result.current.status).toBe('fetched');
  });

  test('it returns errors when the loading failed', async () => {
    // @ts-ignore
    jest.spyOn(global, 'fetch').mockReject(new Error('An unexpected server error'));

    const {result} = renderUseFetchData('/fetch/bad-data');

    await act(async () => {
      result.current.fetch();
    });

    expect(result.current.data).toBeNull();
    expect(result.current.status).toEqual('error');
    expect(result.current.error).toMatch(/unexpected server error/);
  });
});
