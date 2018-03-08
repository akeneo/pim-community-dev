import {catalogChannelChanged, catalogLocaleChanged} from 'pimfront/app/domain/event/user';
import Channel from 'pimfront/app/domain/model/channel';
import Locale from 'pimfront/app/domain/model/locale';
import {updateResults} from 'pimfront/product-grid/application/action/search';

export const gridChannelChanged = (channel: Channel) => (dispatch: any, getState: any): void => {
  dispatch(catalogChannelChanged(channel.code));
  const state = getState();
  if (!channel.locales.find((locale: Locale) => locale.code === state.user.catalogLocale)) {
    dispatch(catalogLocaleChanged(channel.locales[0].code));
  }

  return dispatch(updateResults());
};
