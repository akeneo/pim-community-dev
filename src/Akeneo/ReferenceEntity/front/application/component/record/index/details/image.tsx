import * as React from 'react';
import {NormalizedValue} from 'akeneoreferenceentity/domain/model/record/value';
import {denormalizeFile} from 'akeneoreferenceentity/domain/model/file';
import {getImageShowUrl} from 'akeneoreferenceentity/tools/media-url-generator';
import {CellView} from 'akeneoreferenceentity/application/configuration/value';
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
