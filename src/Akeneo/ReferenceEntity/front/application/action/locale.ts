import {localesReceived} from 'akeneoreferenceentity/domain/event/locale';
import localeFetcher from 'akeneoreferenceentity/infrastructure/fetcher/locale';

export const updateActivatedLocales = () => async (dispatch: any): Promise<void> => {
  const locales = await localeFetcher.fetchActivated();

  dispatch(localesReceived(locales));
};
