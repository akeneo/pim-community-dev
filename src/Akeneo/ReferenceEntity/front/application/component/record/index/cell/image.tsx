import React, {memo} from 'react';
import {NormalizedValue} from 'akeneoreferenceentity/domain/model/record/value';
import {denormalizeFile} from 'akeneoreferenceentity/domain/model/file';
import {getImageShowUrl} from 'akeneoreferenceentity/tools/media-url-generator';
import {CellView} from 'akeneoreferenceentity/application/configuration/value';
import {LazyLoadedImage} from 'akeneoreferenceentity/application/component/app/lazy-loaded-image';

const ImageCellView: CellView = memo(({value}: {value: NormalizedValue}) => (
  <div className="AknGrid-bodyCellContainer">
    <LazyLoadedImage
      className="AknGrid-image"
      src={getImageShowUrl(denormalizeFile(value.data), 'thumbnail_small')}
      width={44}
      height={44}
    />
  </div>
));

export const cell = ImageCellView;
