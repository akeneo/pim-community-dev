import * as React from 'react';
import {NormalizedValue} from 'akeneoreferenceentity/domain/model/record/value';
import {CellView} from 'akeneoreferenceentity/application/configuration/value';
const memo = (React as any).memo;

const TextCellView: CellView = memo(({value}: {value: NormalizedValue}) => {
  const text = undefined === value ? '' : value.data;

  return (
    <div className="AknGrid-bodyCellContainer" title={text}>
      {text}
    </div>
  );
});

export const cell = TextCellView;
