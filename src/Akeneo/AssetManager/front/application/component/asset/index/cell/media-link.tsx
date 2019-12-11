import * as React from 'react';
import {NormalizedValue} from 'akeneoassetmanager/domain/model/asset/value';
import {CellView} from 'akeneoassetmanager/application/configuration/value';
import {Column} from 'akeneoassetmanager/application/reducer/grid';
import {NormalizedFileData} from 'akeneoassetmanager/domain/model/asset/data/media-file';
import {getFileThumbnailUrl} from 'akeneoassetmanager/tools/media-url-generator';

const memo = (React as any).memo;

const MediaLinkCellView: CellView = memo(({value, column}: {value: NormalizedValue; column: Column}) => {
  const file = value.data as NormalizedFileData;

  return (
    <div className="AknGrid-bodyCellContainer" title={file ? file.originalFilename : ''}>
      <img
        className="AknGrid-image AknLoadingPlaceHolder"
        width="44"
        height="44"
        src={getFileThumbnailUrl(column.attribute.identifier, file)}
      />
    </div>
  );
});

export const cell = MediaLinkCellView;
