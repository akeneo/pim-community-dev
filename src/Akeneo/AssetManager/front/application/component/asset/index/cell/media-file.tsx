import * as React from 'react';
import {CellView} from 'akeneoassetmanager/application/configuration/value';
import {Column} from 'akeneoassetmanager/application/reducer/grid';
import {getMediaPreviewUrl} from 'akeneoassetmanager/tools/media-url-generator';
import {isMediaFileData} from 'akeneoassetmanager/domain/model/asset/data/media-file';
import ListValue from 'akeneoassetmanager/domain/model/asset/list-value';
import {isMediaFileAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-file';
import {getMediaData} from 'akeneoassetmanager/domain/model/asset/data';
import {MediaPreviewType} from 'akeneoassetmanager/domain/model/asset/media-preview';
const memo = (React as any).memo;

const MediaFileCellView: CellView = memo(({value, column}: {value: ListValue; column: Column}) => {
  if (!isMediaFileData(value.data)) return null;
  if (!isMediaFileAttribute(column.attribute)) return null;

  return (
    <div className="AknGrid-bodyCellContainer">
      <img
        className="AknGrid-image AknLoadingPlaceHolder"
        width="44"
        height="44"
        src={getMediaPreviewUrl({
          type: MediaPreviewType.Thumbnail,
          attributeIdentifier: column.attribute.identifier,
          data: getMediaData(value.data, column.attribute),
        })}
      />
    </div>
  );
});

export const cell = MediaFileCellView;
