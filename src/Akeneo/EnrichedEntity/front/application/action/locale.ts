import {localesReceived} from 'akeneoenrichedentity/domain/event/locale';
import localeFetcher from 'akeneoenrichedentity/infrastructure/fetcher/locale';

export const updateActivatedLocales = () => async (dispatch: any): Promise<void> => {
  const locales = await localeFetcher.fetchActivated();

  dispatch(localesReceived(locales));
};
