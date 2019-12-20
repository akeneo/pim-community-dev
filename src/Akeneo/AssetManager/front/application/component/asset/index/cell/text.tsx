import * as React from 'react';
import {CellView} from 'akeneoassetmanager/application/configuration/value';
import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';
import {textDataStringValue, isTextData} from 'akeneoassetmanager/domain/model/asset/data/text';
const memo = (React as any).memo;

const TextCellView: CellView = memo(({value}: {value: EditionValue}) => {
  if (!isTextData(value.data)) return null;

  return (
    <div className="AknGrid-bodyCellContainer" title={textDataStringValue(value.data)}>
      {textDataStringValue(value.data)}
    </div>
  );
});

export const cell = TextCellView;
