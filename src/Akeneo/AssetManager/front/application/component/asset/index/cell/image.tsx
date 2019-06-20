import * as React from 'react';
import {NormalizedValue} from 'akeneoassetmanager/domain/model/asset/value';
import {denormalizeFile} from 'akeneoassetmanager/domain/model/file';
import {getImageShowUrl} from 'akeneoassetmanager/tools/media-url-generator';
import {CellView} from 'akeneoassetmanager/application/configuration/value';
const memo = (React as any).memo;

const ImageCellView: CellView = memo(({value}: {value: NormalizedValue}) => {
  return (
    <div className="AknGrid-bodyCellContainer">
      <img
        className="AknGrid-image AknLoadingPlaceHolder"
        width="44"
        height="44"
        src={getImageShowUrl(denormalizeFile(value.data), 'thumbnail_small')}
      />
    </div>
  );
});

export const cell = ImageCellView;
