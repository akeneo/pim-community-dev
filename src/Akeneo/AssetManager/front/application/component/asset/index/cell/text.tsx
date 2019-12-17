import * as React from 'react';
import {CellView} from 'akeneoassetmanager/application/configuration/value';
import Value from 'akeneoassetmanager/domain/model/asset/value';
import {textDataStringValue, isTextData} from 'akeneoassetmanager/domain/model/asset/data/text';
const memo = (React as any).memo;

const TextCellView: CellView = memo(({value}: {value: Value}) => {
  if (!isTextData(value.data)) return null;

  return (
    <div className="AknGrid-bodyCellContainer" title={textDataStringValue(value.data)}>
      {textDataStringValue(value.data)}
    </div>
  );
});

export const cell = TextCellView;
