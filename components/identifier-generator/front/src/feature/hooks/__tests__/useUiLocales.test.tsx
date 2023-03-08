import {renderHook} from '@testing-library/react-hooks';
import {createWrapper} from '../../tests/hooks/config/createWrapper';
import {act} from 'react-dom/test-utils';
import {UiLocale} from '../../models';
import {useUiLocales} from '../useUiLocales';
import uiLocales from '../../tests/fixtures/uiLocales';

describe('useUiLocales', () => {
  test('it retrieves ui locales list', async () => {
    const {result, waitFor} = renderHook<
      null,
      {
        isSuccess: boolean;
        data?: UiLocale[] | undefined;
        error: Error | null;
      }
    >(() => useUiLocales(), {
      wrapper: createWrapper(),
    });

    await waitFor(() => result.current.isSuccess);

    act(() => {
      expect(result.current.data).toBeDefined();
      expect(result.current.data).toEqual(uiLocales);
    });
  });
});
