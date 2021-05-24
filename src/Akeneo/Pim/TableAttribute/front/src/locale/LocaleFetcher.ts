import { Locale, LocaleCode, Router } from "@akeneo-pim-community/shared";

const fetchLocale = async (router: Router, code: LocaleCode): Promise<Locale | undefined> => {
  const url = router.generate('pim_enrich_locale_rest_index');
  const response = await fetch(url);

  const result = await response.json() as Locale[];

  return result.find(locale => locale.code === code);
};

const fetchActivatedLocales = async (router: Router): Promise<Locale[]> => {
  const url = router.generate('pim_enrich_locale_rest_index', {
    activated: true,
  });
  const response = await fetch(url);

  return await response.json();
};

const fetchUiLocales = async (router: Router): Promise<Locale[]> => {
  const url = router.generate('pim_localization_locale_index');
  const response = await fetch(url);

  return await response.json();
};

export {fetchActivatedLocales, fetchUiLocales, fetchLocale};
