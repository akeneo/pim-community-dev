import {useSetPageTitle} from './useSetPageTitle';
import {renderHookWithProviders} from '../tests';

describe('useSetPageTitle', () => {
  test('it updates the document title', () => {
    const title = 'a title';
    renderHookWithProviders(() => useSetPageTitle(title));

    expect(document.title).toBe(title);
  });
});
