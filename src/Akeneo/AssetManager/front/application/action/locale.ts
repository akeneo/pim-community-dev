import {localesReceived} from 'akeneoassetmanager/domain/event/locale';
import Locale from 'akeneoassetmanager/domain/model/locale';

const fetcherRegistry = require('pim/fetcher-registry');

export const updateActivatedLocales = () => async (dispatch: any): Promise<void> => {
  fetcherRegistry
    .getFetcher('locale')
    .fetchActivated({filter_locales: false})
    .then((locales: Locale[]) => {
      dispatch(localesReceived(locales));
    });
};
