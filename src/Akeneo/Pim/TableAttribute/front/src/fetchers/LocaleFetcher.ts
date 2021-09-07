import {Locale, Router} from '@akeneo-pim-community/shared';

const fetchLocales = async (router: Router): Promise<Locale[]> => {
  const url = router.generate('pim_enrich_locale_rest_index');
  const response = await fetch(url);

  return (await response.json()) as Locale[];
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

const LocaleFetcher = {
  fetchAll: fetchLocales,
  fetchActivated: fetchActivatedLocales,
  fetchUi: fetchUiLocales,
};

export {LocaleFetcher};
