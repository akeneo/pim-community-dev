import {catalogLocaleChanged, catalogChannelChanged} from 'akeneoreferenceentity/domain/event/user';
import {updateProductList} from 'akeneoreferenceentity/application/action/product/attribute';
import {EditState} from 'akeneoreferenceentity/application/reducer/record/edit';

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
