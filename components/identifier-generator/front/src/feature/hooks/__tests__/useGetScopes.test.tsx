import mockedScopes from '../../tests/fixtures/scopes';
import {renderHook} from '@testing-library/react-hooks';
import {useGetScopes} from '../useGetScopes';
import {createWrapper} from '../../tests/hooks/config/createWrapper';

describe('useGetScopes', () => {
  it('retrieves scopes list', async () => {
    const {result, waitFor} = renderHook(() => useGetScopes(), {wrapper: createWrapper()});

    await waitFor(() => !!result.current.data);

    expect(result.current.data).toEqual(mockedScopes);
  });
});
