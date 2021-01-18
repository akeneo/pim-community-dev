import { Locale, useMountedRef } from "@akeneo-pim-community/settings-ui";
import { useCallback, useEffect, useState } from "react";
import { fetchLocalesDictionaryInfo } from "../../infrastructure";
import { LocalesDictionaryInfoCollection } from "../../domain";

export type GetDictionaryTotalWords = (locale: Locale) => number | undefined;

export type LocalesDictionaryInfoState = {
  localesDictionaryInfo: LocalesDictionaryInfoCollection;
  getDictionaryTotalWords: GetDictionaryTotalWords;
};

const FeatureFlags = require("pim/feature-flags");

const useLocalesDictionaryInfo = (
  locales: Locale[]
): LocalesDictionaryInfoState => {
  const [localesDictionaryInfo, setLocalesDictionaryInfo] = useState<
    LocalesDictionaryInfoCollection
  >({});
  const mountedRef = useMountedRef();

  const load = useCallback(
    async (localesList: Locale[]) => {
      if (!FeatureFlags.isEnabled("dictionary")) {
        setLocalesDictionaryInfo({});
        return;
      }
      const infos = await fetchLocalesDictionaryInfo(
        localesList.map((locale) => locale.code)
      );

      if (mountedRef.current) {
        setLocalesDictionaryInfo(infos);
      }
    },
    [setLocalesDictionaryInfo]
  );

  const getDictionaryTotalWords: GetDictionaryTotalWords = useCallback(
    (locale: Locale): number | undefined => {
      if (
        !localesDictionaryInfo.hasOwnProperty(locale.code) ||
        localesDictionaryInfo[locale.code] === null
      ) {
        return undefined;
      }

      return localesDictionaryInfo[locale.code];
    },
    [localesDictionaryInfo]
  );

  useEffect(() => {
    load(locales);
  }, [load, locales]);

  return {
    localesDictionaryInfo,
    getDictionaryTotalWords,
  };
};

export { useLocalesDictionaryInfo };
