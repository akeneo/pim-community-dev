import {createLineFromFilename} from 'akeneoassetmanager/application/asset-upload/utils/utils';
import Line from 'akeneoassetmanager/application/asset-upload/model/line';
import {File as FileModel} from 'akeneoassetmanager/domain/model/file';
import {getThumbnailFromFile, uploadFile} from 'akeneoassetmanager/application/asset-upload/utils/file';
import {
  fileThumbnailGenerationDoneAction,
  fileUploadProgressAction,
  fileUploadSuccessAction,
  linesAddedAction,
  fileUploadFailureAction,
} from 'akeneoassetmanager/application/asset-upload/reducer/action';
import notify from 'akeneoassetmanager/tools/notify';
import createQueue from 'p-limit';
import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import Channel from 'akeneoassetmanager/domain/model/channel';
import Locale from 'akeneoassetmanager/domain/model/locale';

const FILES_QUANTITY_LIMIT = 500;

const CONCURRENCY = 5;
const queue = createQueue(CONCURRENCY);

export const onFileDrop = (
  files: File[],
  assetFamily: AssetFamily,
  channels: Channel[],
  locales: Locale[],
  dispatch: (action: any) => void
) => {
  if (null === files || 0 === files.length) {
    return;
  }

  if (files.length > FILES_QUANTITY_LIMIT) {
    files = files.slice(0, FILES_QUANTITY_LIMIT);

    notify('warning', 'pim_asset_manager.asset.upload.files_limit', {limit: FILES_QUANTITY_LIMIT});
  }

  const lines = files.map((file: File) => {
    const filename = file.name;

    const line = createLineFromFilename(filename, assetFamily, channels, locales);
    getThumbnailFromFile(file, line).then(({thumbnail, line}) =>
      dispatch(fileThumbnailGenerationDoneAction(thumbnail, line))
    );

    queue(() =>
      uploadFile(file, line, (line: Line, progress: number) => {
        dispatch(fileUploadProgressAction(line, progress));
      })
    )
      .then((file: FileModel) => {
        dispatch(fileUploadSuccessAction(line, file));
      })
      .catch(() => {
        dispatch(fileUploadFailureAction(line));
      });

    return line;
  });
  dispatch(linesAddedAction(lines));
};
