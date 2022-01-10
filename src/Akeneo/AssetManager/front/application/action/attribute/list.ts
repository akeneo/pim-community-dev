import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import {attributeListUpdated} from 'akeneoassetmanager/domain/event/attribute/list';
import {notifyAttributeListUpdateFailed} from 'akeneoassetmanager/application/action/attribute/notify';
import {AttributeFetcher} from 'akeneoassetmanager/domain/fetcher/attribute';

export const updateAttributeList = (attributeFetcher: AttributeFetcher) => async (
  dispatch: any,
  getState: () => EditState
): Promise<void> => {
  const assetFamily = getState().form.data;
  try {
    const attributes = await attributeFetcher.fetchAll(assetFamily.identifier);
    dispatch(attributeListUpdated(attributes));
  } catch (error) {
    dispatch(notifyAttributeListUpdateFailed());

    throw error;
  }
};
