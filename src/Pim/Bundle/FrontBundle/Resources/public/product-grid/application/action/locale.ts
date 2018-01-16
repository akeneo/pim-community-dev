import { catalogLocaleChanged } from 'pimfront/app/domain/event/user';
import Locale from 'pimfront/app/domain/model/locale';
import { updateResultsAction } from 'pimfront/product-grid/application/action/search';

export const gridLocaleChanged = (locale: Locale) => (dispatch: any, getState: any): void => {
  dispatch(catalogLocaleChanged(locale.code));

  return dispatch(updateResultsAction());
};
