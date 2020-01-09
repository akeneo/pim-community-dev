import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import attributeFetcher from 'akeneoassetmanager/infrastructure/fetcher/attribute';
import {attributeListUpdated} from 'akeneoassetmanager/domain/event/attribute/list';
import {notifyAttributeListUpdateFailed} from 'akeneoassetmanager/application/action/attribute/notify';

export const updateAttributeList = () => async (dispatch: any, getState: () => EditState): Promise<void> => {
  const assetFamily = getState().form.data;
  try {
    const attributes = await attributeFetcher.fetchAll(assetFamily.identifier);
    dispatch(attributeListUpdated(attributes));
  } catch (error) {
    dispatch(notifyAttributeListUpdateFailed());

    throw error;
  }
};
