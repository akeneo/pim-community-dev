import * as React from 'react';
import {CellView} from 'akeneoassetmanager/application/configuration/value';
import {Column} from 'akeneoassetmanager/application/reducer/grid';
import {getFileThumbnailUrl} from 'akeneoassetmanager/tools/media-url-generator';
import {isMediaFileData} from 'akeneoassetmanager/domain/model/asset/data/media-file';
import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';
const memo = (React as any).memo;

const ImageCellView: CellView = memo(({value, column}: {value: EditionValue; column: Column}) => {
  if (!isMediaFileData(value.data)) return null;

  return (
    <div className="AknGrid-bodyCellContainer">
      <img
        className="AknGrid-image AknLoadingPlaceHolder"
        width="44"
        height="44"
        src={getFileThumbnailUrl(column.attribute.identifier, value.data)}
      />
    </div>
  );
});

export const cell = ImageCellView;
