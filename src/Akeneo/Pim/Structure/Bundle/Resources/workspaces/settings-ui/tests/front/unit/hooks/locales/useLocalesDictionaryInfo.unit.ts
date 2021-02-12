import {renderHookWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {Locale} from '@akeneo-pim-community/settings-ui';
import {aLocale} from '@akeneo-pim-community/settings-ui/tests/front/utils/provideLocaleHelper';
import {useLocalesDictionaryInfo} from '@akeneo-pim-enterprise/settings-ui';
import {fetchLocalesDictionaryInfo} from '@akeneo-pim-enterprise/settings-ui/src/infrastructure/fetchers/fetchLocalesDictionaryInfo';

const FeatureFlags = require('pim/feature-flags');
FeatureFlags.isEnabled.mockImplementation((feature: string) => false);

jest.mock('@akeneo-pim-enterprise/settings-ui/src/infrastructure/fetchers/fetchLocalesDictionaryInfo');

describe('useLocalesDictionaryInfo', () => {
  const renderUseLocalesDictionaryInfo = (locales: Locale[]) => {
    return renderHookWithProviders(() => useLocalesDictionaryInfo(locales));
  };

  beforeEach(() => {
    jest.clearAllMocks();
  });

  afterAll(() => {
    jest.resetAllMocks();
    jest.restoreAllMocks();
  });

  test('it loads dictionary info for a list of locales', async () => {
    FeatureFlags.isEnabled.mockImplementation((feature: string) => true);
    const fetchedData = {
      en_US: 0,
      fr_FR: null,
      de_DE: 1234,
    };
    // @ts-ignore
    fetchLocalesDictionaryInfo.mockResolvedValue(fetchedData);

    const localeA = aLocale('en_US');
    const localeB = aLocale('fr_FR');
    const localeC = aLocale('de_DE');
    const localeF = aLocale('es_ES');
    const {result, waitForNextUpdate} = renderUseLocalesDictionaryInfo([localeA, localeB, localeC]);

    expect(result.current.localesDictionaryInfo).toEqual({});

    await waitForNextUpdate();

    expect(fetchLocalesDictionaryInfo).toHaveBeenCalledWith(['en_US', 'fr_FR', 'de_DE']);

    expect(result.current.localesDictionaryInfo).toEqual(fetchedData);
    expect(result.current.getDictionaryTotalWords(localeA.code)).toBe(0);
    expect(result.current.getDictionaryTotalWords(localeB.code)).toBeUndefined();
    expect(result.current.getDictionaryTotalWords(localeC.code)).toBe(1234);
    expect(result.current.getDictionaryTotalWords(localeF.code)).toBeUndefined();
  });

  test('it does not load dictionary info when dictionary feature is disabled', async () => {
    FeatureFlags.isEnabled.mockImplementation((feature: string) => false);

    const localeA = aLocale('en_US');
    const localeB = aLocale('fr_FR');
    const localeC = aLocale('de_DE');
    const localeF = aLocale('es_ES');
    const {result} = renderUseLocalesDictionaryInfo([localeA, localeB, localeC]);

    expect(result.current.localesDictionaryInfo).toEqual({});
    expect(result.current.getDictionaryTotalWords(localeA)).toBeUndefined();
    expect(result.current.getDictionaryTotalWords(localeB)).toBeUndefined();
    expect(result.current.getDictionaryTotalWords(localeC)).toBeUndefined();
    expect(result.current.getDictionaryTotalWords(localeF)).toBeUndefined();

    expect(fetchLocalesDictionaryInfo).not.toHaveBeenCalled();
  });
});
