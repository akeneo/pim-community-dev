import {catalogLocaleChanged, catalogChannelChanged} from 'akeneoassetmanager/domain/event/user';

export const localeChanged = (locale: string) => (dispatch: any) => {
  dispatch(catalogLocaleChanged(locale));
};

export const channelChanged = (channel: string) => (dispatch: any) => {
  dispatch(catalogChannelChanged(channel));
};
