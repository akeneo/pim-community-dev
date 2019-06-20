import {catalogLocaleChanged, catalogChannelChanged} from 'akeneoassetmanager/domain/event/user';
import {updateProductList} from 'akeneoassetmanager/application/action/product/attribute';
import {EditState} from 'akeneoassetmanager/application/reducer/asset/edit';

export const localeChanged = (locale: string) => (dispatch: any, getState: () => EditState) => {
  dispatch(catalogLocaleChanged(locale));
  if ('product' === getState().sidebar.currentTab) {
    dispatch(updateProductList());
  }
};

export const channelChanged = (channel: string) => (dispatch: any, getState: () => EditState) => {
  dispatch(catalogChannelChanged(channel));
  if ('product' === getState().sidebar.currentTab) {
    dispatch(updateProductList());
  }
};
