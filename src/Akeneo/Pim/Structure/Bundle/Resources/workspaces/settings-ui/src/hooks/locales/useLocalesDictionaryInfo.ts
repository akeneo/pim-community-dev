import {Locale} from '@akeneo-pim-community/settings-ui';
import {useCallback, useEffect, useState} from 'react';
import {fetchLocalesDictionaryInfo} from '../../infrastructure';
import {LocalesDictionaryInfoCollection} from '../../domain';

export type GetDictionaryTotalWords = (localeCode: string) => number | undefined;

export type LocalesDictionaryInfoState = {
  localesDictionaryInfo: LocalesDictionaryInfoCollection;
  getDictionaryTotalWords: GetDictionaryTotalWords;
  refresh: () => void;
};

const FeatureFlags = require('pim/feature-flags');

const useLocalesDictionaryInfo = (locales: Locale[]): LocalesDictionaryInfoState => {
  const [localesDictionaryInfo, setLocalesDictionaryInfo] = useState<LocalesDictionaryInfoCollection>({});

  const load = useCallback(
    async (localesList: Locale[]) => {
      if (!FeatureFlags.isEnabled('dictionary')) {
        setLocalesDictionaryInfo({});
        return;
      }
      const infos = await fetchLocalesDictionaryInfo(localesList.map(locale => locale.code));

      setLocalesDictionaryInfo(infos);
    },
    [setLocalesDictionaryInfo]
  );

  const refresh = useCallback(() => {
    load(locales);
  }, [load, locales]);

  const getDictionaryTotalWords: GetDictionaryTotalWords = useCallback(
    (localeCode: string): number | undefined => {
      if (!localesDictionaryInfo.hasOwnProperty(localeCode) || localesDictionaryInfo[localeCode] === null) {
        return undefined;
      }

      return localesDictionaryInfo[localeCode];
    },
    [localesDictionaryInfo]
  );

  useEffect(() => {
    load(locales);
  }, [load, locales]);

  return {
    localesDictionaryInfo,
    getDictionaryTotalWords,
    refresh,
  };
};

export {useLocalesDictionaryInfo};
