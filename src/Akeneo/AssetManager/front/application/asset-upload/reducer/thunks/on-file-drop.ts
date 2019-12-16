import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {createLineFromFilename} from 'akeneoassetmanager/application/asset-upload/utils/utils';
import Line from 'akeneoassetmanager/application/asset-upload/model/line';
import {File as FileModel} from 'akeneoassetmanager/domain/model/file';
import {getThumbnailFromFile, uploadFile} from 'akeneoassetmanager/application/asset-upload/utils/file';
import {
  fileThumbnailGenerationDoneAction,
  fileUploadProgressAction,
  fileUploadSuccessAction,
  linesAddedAction,
} from 'akeneoassetmanager/application/asset-upload/reducer/action';
import {addQueueSupport} from 'akeneoassetmanager/tools/queue';

const MAX_QUEUE_SIZE = 5;
const queuedUploadFile = addQueueSupport(uploadFile, MAX_QUEUE_SIZE);

export const onFileDrop = (files: File[], assetFamily: AssetFamily, dispatch: (action: any) => void) => {
  if (null === files || 0 === files.length) {
    return;
  }

  const lines = files.map((file: File) => {
    const filename = file.name;

    const line = createLineFromFilename(filename, assetFamily);
    getThumbnailFromFile(file, line).then(({thumbnail, line}) =>
      dispatch(fileThumbnailGenerationDoneAction(thumbnail, line))
    );

    queuedUploadFile(file, line, (line: Line, progress: number) => {
      dispatch(fileUploadProgressAction(line, progress));
    }).then((file: FileModel) => {
      dispatch(fileUploadSuccessAction(line, file));
    });

    return line;
  });
  dispatch(linesAddedAction(lines));
};
