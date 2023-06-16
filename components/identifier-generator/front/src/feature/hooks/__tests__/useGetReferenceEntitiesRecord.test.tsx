import {renderHook} from '@testing-library/react-hooks';
import {createWrapper} from '../../tests/hooks/config/createWrapper';
import {ServerError} from '../../errors';
import {useGetReferenceEntitiesRecord} from '../useGetReferenceEntitiesRecord';

describe('useGetReferenceEntitiesRecord', () => {
  it('should retrieve an error when endpoint returns 500', async () => {
    const fetchImplementation = jest.fn().mockImplementation((requestUrl: string) => {
      if (requestUrl === 'akeneo_reference_entities_record_index_rest') {
        return Promise.resolve({
          ok: false,
        } as Response);
      } else if (requestUrl === 'pim_enrich_attribute_rest_get') {
        return Promise.resolve({
          ok: true,
          json: () =>
            Promise.resolve({
              code: 'test',
              labels: {},
              localizable: false,
              scopable: false,
              type: 'test',
            }),
          status: 200,
        } as Response);
      }
      throw new Error(`Unknown url ${JSON.stringify(requestUrl)}`);
    });
    jest.spyOn(global, 'fetch').mockImplementation(fetchImplementation);

    const {result, waitFor} = renderHook(
      () => useGetReferenceEntitiesRecord({attributeCode: 'attribute', enabled: true}),
      {
        wrapper: createWrapper(),
      }
    );

    await waitFor(() => !!result.current.error);

    expect(result.current.error).toBeDefined();
    expect(result.current.error).toBeInstanceOf(ServerError);
  });
});
