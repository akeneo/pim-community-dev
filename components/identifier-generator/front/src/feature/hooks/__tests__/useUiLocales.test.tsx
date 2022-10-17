import {waitFor} from '@testing-library/react';
import {renderHook} from '@testing-library/react-hooks';
import {createWrapper} from '../../tests/hooks/config/createWrapper';
import {act} from 'react-dom/test-utils';
import {UiLocale} from '../../models/ui-locale';
import {useUiLocales} from '../useUiLocales';

const uiLocales = [{
  id: 42,
  code: 'en_US',
  label: 'English (United States)',
  region: 'United States',
  language: 'English',
}, {
  id: 69,
  code: 'fr_FR',
  label: 'French (France)',
  region: 'France',
  language: 'French',
}, {
  id: 96,
  code: 'de_DE',
  label: 'German (Germany)',
  region: 'Germany',
  language: 'German',
}];

describe('useUiLocales', () => {
  beforeEach(() => {
    jest.spyOn(global, 'fetch').mockResolvedValue({
      ok: true,
      json: () => Promise.resolve(uiLocales),
    } as Response);
  });

  test('it retrieves ui locales list', async () => {
    const {result} = renderHook<null, {
      isSuccess: boolean;
      data?: UiLocale[] | undefined;
      error: Error | null
    }>(
      () => useUiLocales(),
      {
        wrapper: createWrapper(),
      }
    );

    await waitFor(() => result.current.isSuccess);

    act(() => {
      expect(result.current.data).toBeDefined();
      expect(result.current.data).toEqual(uiLocales);
    });
  });
});
