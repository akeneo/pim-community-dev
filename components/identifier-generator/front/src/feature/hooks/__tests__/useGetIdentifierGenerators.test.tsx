import {renderHook} from '@testing-library/react-hooks';
import {useGetIdentifierGenerators} from '../useGetIdentifierGenerators';
import {createWrapper} from '../../tests/hooks/config/createWrapper';
import {ServerError} from '../../errors';
import {mockResponse} from '../../tests/test-utils';

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

describe('useGetIdentifierGenerators', () => {
  test('it retrieves generators list', async () => {
    mockResponse('akeneo_identifier_generator_rest_list', 'GET', {ok: true, json: list});

    const {result, waitFor} = renderHook(() => useGetIdentifierGenerators(), {wrapper: createWrapper()});

    await waitFor(() => !!result.current.data);

    expect(result.current.data).toBeDefined();
    expect(result.current.data).toEqual(list);
  });

  test('it fails and retrieves no data', async () => {
    mockResponse('akeneo_identifier_generator_rest_list', 'GET', {ok: false, json: {}});

    const {result, waitFor} = renderHook(() => useGetIdentifierGenerators(), {wrapper: createWrapper()});

    await waitFor(() => !!result.current.error);

    expect(result.current.error).toBeDefined();
    expect(result.current.error).toBeInstanceOf(ServerError);
  });
});
