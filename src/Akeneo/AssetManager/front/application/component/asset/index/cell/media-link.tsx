import * as React from 'react';
import {CellView} from 'akeneoassetmanager/application/configuration/value';
import {Column} from 'akeneoassetmanager/application/reducer/grid';
import {isMediaLinkData, mediaLinkDataStringValue} from 'akeneoassetmanager/domain/model/asset/data/media-link';
import {isMediaLinkAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {MediaPreviewType, getMediaPreviewUrl} from 'akeneoassetmanager/tools/media-url-generator';
import ListValue from 'akeneoassetmanager/domain/model/asset/list-value';
import {getValueData} from 'akeneoassetmanager/domain/model/asset/data';

const memo = (React as any).memo;

const MediaLinkCellView: CellView = memo(({value, column}: {value: ListValue; column: Column}) => {
  if (!isMediaLinkData(value.data)) return null;
  if (!isMediaLinkAttribute(column.attribute)) return null;

  return (
    <div className="AknGrid-bodyCellContainer" title={mediaLinkDataStringValue(value.data)}>
      <img
        className="AknGrid-image AknLoadingPlaceHolder"
        width="44"
        height="44"
        src={getMediaPreviewUrl({
          type: MediaPreviewType.Thumbnail,
          attributeIdentifier: column.attribute.identifier,
          data: getValueData(value, column.attribute),
        })}
      />
    </div>
  );
});

export const cell = MediaLinkCellView;
