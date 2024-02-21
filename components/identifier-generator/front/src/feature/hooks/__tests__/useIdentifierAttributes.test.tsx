import {renderHook} from '@testing-library/react-hooks';
import {useIdentifierAttributes} from '../';
import {createWrapper} from '../../tests/hooks/config/createWrapper';
import {FlattenAttribute} from '../../models/';
import {mockResponse} from '../../tests/test-utils';

describe('useIdentifierAttributes', () => {
  test('it retrieves identifier attribute list', async () => {
    const expectCall = mockResponse('akeneo_identifier_generator_get_identifier_attributes', 'GET', {
      json: [{code: 'sku', label: 'Sku'}],
    });

    const {result, waitFor} = renderHook<
      null,
      {
        data?: FlattenAttribute[] | undefined;
        error: Error | null;
      }
    >(() => useIdentifierAttributes(), {
      wrapper: createWrapper(),
    });

    await waitFor(() => !!result.current.data);

    expect(result.current.data).toBeDefined();
    expect(result.current.data).toEqual([{code: 'sku', label: 'Sku'}]);

    expectCall();
  });
});
