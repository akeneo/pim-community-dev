import {renderHook} from '@testing-library/react-hooks';
import {useGetGenerators} from '../useGetGenerators';
import {createWrapper} from '../../tests/hooks/config/createWrapper';
import {ServerError} from '../../errors';

const list = [
  {
    uuid: '2e87349c-e801-4b06-9fb9-755043f87c9a',
    code: 'test',
    conditions: [],
    structure: [{type: 'free_text', string: 'AKN'}],
    labels: {ca_ES: 'azeaze', en_US: 'test'},
    target: 'sku',
    delimiter: null,
  },
];

describe('useGetGenerators', () => {
  test('it retrieves generators list', async () => {
    jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: true,
      json: () => Promise.resolve(list),
    } as Response);

    const {result, waitFor} = renderHook(() => useGetGenerators(), {wrapper: createWrapper()});

    await waitFor(() => !!result.current.data);

    expect(result.current.data).toBeDefined();
    expect(result.current.data).toEqual(list);
  });

  test('it fails and retrieves no data', async () => {
    const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
    jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: false,
      json: () => Promise.resolve('error message'),
    } as Response);

    const {result, waitFor} = renderHook(() => useGetGenerators(), {wrapper: createWrapper()});

    await waitFor(() => !!result.current.error);

    expect(result.current.error).toBeDefined();
    expect(result.current.error).toBeInstanceOf(ServerError);
    mockedConsole.mockRestore();
  });
});
