import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import Line from 'akeneoassetmanager/application/asset-upload/model/line';
import {createAssetsFromLines, selectLinesToSend} from 'akeneoassetmanager/application/asset-upload/utils/utils';
import {
  assetCreationFailAction,
  assetCreationSuccessAction,
  lineCreationStartAction,
} from 'akeneoassetmanager/application/asset-upload/reducer/action';
import {CreationAsset} from 'akeneoassetmanager/application/asset-upload/model/asset';
import {create} from 'akeneoassetmanager/application/asset-upload/saver/asset';

export const onCreateAllAsset = (assetFamily: AssetFamily, lines: Line[], dispatch: (action: any) => void) => {
  const linesToSend = selectLinesToSend(lines);
  const assetsToSend = createAssetsFromLines(linesToSend, assetFamily);

  linesToSend.forEach((line: Line) => dispatch(lineCreationStartAction(line)));

  assetsToSend.forEach(async (asset: CreationAsset) => {
    try {
      const result = await create(asset);
      if (null !== result) {
        dispatch(assetCreationFailAction(asset, result));
      } else {
        dispatch(assetCreationSuccessAction(asset));
      }
    } catch (e) {
      dispatch(
        assetCreationFailAction(asset, [
          {
            messageTemplate: 'pim_asset_manager.asset.validation.server_error',
            parameters: {},
            message: 'Internal server error',
            propertyPath: '',
            invalidValue: asset,
          },
        ])
      );
    }
  });
};
