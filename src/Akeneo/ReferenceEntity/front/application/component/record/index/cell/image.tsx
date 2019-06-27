import * as React from 'react';
import {NormalizedValue} from 'akeneoreferenceentity/domain/model/record/value';
import {CellView} from 'akeneoreferenceentity/application/configuration/value';
const memo = (React as any).memo;

const ImageCellView: CellView = memo(({value}: {value: NormalizedValue}) => {
  return (
    <div className="AknGrid-bodyCellContainer">
      <img className="AknGrid-image AknLoadingPlaceHolder" width="44" height="44" src={value.data} />
    </div>
  );
});

export const cell = ImageCellView;
