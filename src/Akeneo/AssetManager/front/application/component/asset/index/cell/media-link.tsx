import * as React from 'react';
import {CellView} from 'akeneoassetmanager/application/configuration/value';
import {Column} from 'akeneoassetmanager/application/reducer/grid';
import {getMediaLinkPreviewUrl, MediaPreviewTypes} from 'akeneoassetmanager/tools/media-url-generator';
import {isMediaLinkData, mediaLinkDataStringValue} from 'akeneoassetmanager/domain/model/asset/data/media-link';
import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';
import {isMediaLinkAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-link';

const memo = (React as any).memo;

const MediaLinkCellView: CellView = memo(({value, column}: {value: EditionValue; column: Column}) => {
  if (!isMediaLinkData(value.data)) return null;
  if (!isMediaLinkAttribute(column.attribute)) return null;

  return (
    <div className="AknGrid-bodyCellContainer" title={mediaLinkDataStringValue(value.data)}>
      <img
        className="AknGrid-image AknLoadingPlaceHolder"
        width="44"
        height="44"
        src={getMediaLinkPreviewUrl(MediaPreviewTypes.Thumbnail, value.data, column.attribute)}
      />
    </div>
  );
});

export const cell = MediaLinkCellView;
