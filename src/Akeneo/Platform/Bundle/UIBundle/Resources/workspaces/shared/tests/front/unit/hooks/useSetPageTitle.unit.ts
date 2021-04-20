import {renderHookWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {useSetPageTitle} from '@akeneo-pim-community/shared';

describe('useSetPageTitle', () => {
  test('it updates the document title', () => {
    const title = 'a title';
    renderHookWithProviders(() => useSetPageTitle(title));

    expect(document.title).toBe(title);
  });
});
