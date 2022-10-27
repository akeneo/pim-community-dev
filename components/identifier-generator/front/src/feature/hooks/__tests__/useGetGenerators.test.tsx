import {renderHook} from '@testing-library/react-hooks';
import {useGetGenerators} from '../useGetGenerators';
import {createWrapper} from '../../tests/hooks/config/createWrapper';

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
});
