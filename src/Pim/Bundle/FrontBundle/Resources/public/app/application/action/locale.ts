const fetcherRegistry = require('pim/fetcher-registry');
import LocaleInterface, { createLocale } from 'pimfront/app/domain/model/locale';
import { localesUpdated } from 'pimfront/app/domain/event/locale';
import hidrateAll from 'pimfront/app/application/hidrator/hidrator';

const hidrator = (locale: any): LocaleInterface => {
  return createLocale(locale);
};

export const updateLocales = () => (dispatch: any): void => {
  return fetcherRegistry.getFetcher('locale').fetchActivated()
    .then((locales: LocaleInterface[]) => {
      dispatch(localesUpdated(hidrateAll<LocaleInterface>(hidrator)(locales)));
    });
};
