const fetcherRegistry = require('pim/fetcher-registry');
import LocaleInterface, {createLocale} from 'pimfront/app/domain/model/locale';
import {localesUpdated} from 'pimfront/app/domain/event/locale';
import hidrateAll from 'pimfront/app/application/hidrator/hidrator';

const hidrator = (locale: any): LocaleInterface => {
  return createLocale(locale);
};

export const updateLocales = () => async (dispatch: any): Promise<void> => {
  const locales = await fetcherRegistry.getFetcher('locale').fetchActivated();

  dispatch(localesUpdated(hidrateAll<LocaleInterface>(hidrator)(locales)));
};
