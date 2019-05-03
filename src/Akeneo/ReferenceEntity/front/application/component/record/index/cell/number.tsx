import * as React from 'react';
import {NormalizedValue} from 'akeneoreferenceentity/domain/model/record/value';
import {CellView} from 'akeneoreferenceentity/application/configuration/value';
const memo = (React as any).memo;

const NumberCellView: CellView = memo(({value}: {value: NormalizedValue}) => {
  const number = undefined === value ? '' : value.data;

  return (
    <div className="AknGrid-bodyCellContainer" title={number}>
      {number}
    </div>
  );
});

export const cell = NumberCellView;
