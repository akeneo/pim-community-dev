import * as React from 'react';
import {NormalizedValue} from 'akeneoassetmanager/domain/model/asset/value';
import {CellView} from 'akeneoassetmanager/application/configuration/value';
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
