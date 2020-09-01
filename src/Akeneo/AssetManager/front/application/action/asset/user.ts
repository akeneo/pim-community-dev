import {catalogLocaleChanged, catalogChannelChanged} from 'akeneoassetmanager/domain/event/user';
import {updateProductList} from 'akeneoassetmanager/application/action/product/attribute';

export const localeChanged = (locale: string) => (dispatch: any) => {
  dispatch(catalogLocaleChanged(locale));
  dispatch(updateProductList());
};

export const channelChanged = (channel: string) => (dispatch: any) => {
  dispatch(catalogChannelChanged(channel));
  dispatch(updateProductList());
};
