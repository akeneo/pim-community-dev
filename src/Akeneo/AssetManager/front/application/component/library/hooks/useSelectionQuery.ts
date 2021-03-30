import {createQuery, addSelection} from 'akeneoassetmanager/application/hooks/grid';
import {Filter} from 'akeneoassetmanager/application/reducer/grid';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {Context} from 'akeneoassetmanager/domain/model/context';
import {Selection} from 'akeneo-design-system';

const useSelectionQuery = (
  currentAssetFamilyIdentifier: string | null,
  filterCollection: Filter[],
  searchValue: string,
  context: Context,
  selection: Selection<AssetCode>
) => {
  if (null === currentAssetFamilyIdentifier) {
    return null;
  }

  const query = createQuery(
    currentAssetFamilyIdentifier,
    filterCollection,
    searchValue,
    [],
    context.channel,
    context.locale,
    0,
    50
  );

  return addSelection(query, selection);
};

export {useSelectionQuery};
