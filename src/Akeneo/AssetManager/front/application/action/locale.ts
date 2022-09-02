import {localesReceived} from 'akeneoassetmanager/domain/event/locale';
import Locale from 'akeneoassetmanager/domain/model/locale';

const updateActivatedLocales = (localeFetcher: {
  fetchActivated: (options: {filter_locales: boolean}) => Promise<Locale[]>;
}) => async (dispatch: any): Promise<void> => {
  localeFetcher.fetchActivated({filter_locales: false}).then((locales: Locale[]) => {
    dispatch(localesReceived(locales));
  });
};

export {updateActivatedLocales};
