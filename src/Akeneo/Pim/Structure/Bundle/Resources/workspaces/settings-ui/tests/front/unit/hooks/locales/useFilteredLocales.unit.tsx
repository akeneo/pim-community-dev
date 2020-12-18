import React from 'react';
import {renderHookWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {useFilteredLocales} from '@akeneo-pim-community/settings-ui/src/hooks/locales';
import {aListOfLocales, aLocale} from '../../../utils/provideLocaleHelper';
import {Locale} from '../../../../../src/models';

import {act} from 'react-test-renderer';

describe('useFilteredLocales', () => {
  const renderUseFilteredLocales = (locales: Locale[]) => {
    return renderHookWithProviders(() => useFilteredLocales(locales));
  };

  test('it returns all locales when there are no filter', async () => {
    const locales = aListOfLocales(['en_US', 'fr_FR', 'de_DE']);
    const {result} = renderUseFilteredLocales(locales);

    expect(result.current.filteredLocales).toEqual(locales);
  });

  test('it returns locales with code containing the search text value', async () => {
    const localeA = aLocale('en_US');
    const localeB = aLocale('en_GB');
    const localeC = aLocale('en_AU');
    const localeD = aLocale('fr_FR');
    const localeE = aLocale('de_DE');
    const {result} = renderUseFilteredLocales([localeA, localeB, localeC, localeD, localeE]);

    act(() => {
      result.current.search('en');
    });
    expect(result.current.filteredLocales).toEqual([localeA, localeB, localeC]);

    act(() => {
      result.current.search('a');
    });
    expect(result.current.filteredLocales).toEqual([localeC]);
  });
});
