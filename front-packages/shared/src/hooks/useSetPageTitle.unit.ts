import {renderHookWithProviders} from '../../tests/utils';
import {useSetPageTitle} from './useSetPageTitle';

describe('useSetPageTitle', () => {
  test('it updates the document title', () => {
    const title = 'a title';
    renderHookWithProviders(() => useSetPageTitle(title));

    expect(document.title).toBe(title);
  });
});
