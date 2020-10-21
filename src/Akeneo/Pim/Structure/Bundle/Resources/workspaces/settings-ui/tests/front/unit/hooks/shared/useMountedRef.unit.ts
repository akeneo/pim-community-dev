import {renderHook} from '@testing-library/react-hooks';
import {useMountedRef} from '@akeneo-pim-community/settings-ui/src/hooks';

describe('useMountedRef', () => {
  test('it checks the mount state', () => {
    const {result, unmount, waitFor} = renderHook(() => useMountedRef());

    expect(result.current).toBeTruthy();

    unmount();

    waitFor(() => {
      expect(result.current).toBeFalsy();
    });
  });
});
