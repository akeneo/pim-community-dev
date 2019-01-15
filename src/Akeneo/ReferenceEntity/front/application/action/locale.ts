import {localesReceived} from 'akeneoreferenceentity/domain/event/locale';
import Locale from 'akeneoreferenceentity/domain/model/locale';

const fetcherRegistry = require('pim/fetcher-registry');

export const updateActivatedLocales = () => async (dispatch: any): Promise<void> => {
  fetcherRegistry
    .getFetcher('locale')
    .fetchActivated({filter_locales: false})
    .then((locales: Locale[]) => {
      dispatch(localesReceived(locales));
    });
};
