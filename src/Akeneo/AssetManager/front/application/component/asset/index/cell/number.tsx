import * as React from 'react';
import {CellView} from 'akeneoassetmanager/application/configuration/value';
import Value from 'akeneoassetmanager/domain/model/asset/value';
import {numberDataStringValue, isNumberData} from 'akeneoassetmanager/domain/model/asset/data/number';

const UserContext = require('pim/user-context');
const memo = (React as any).memo;

const NumberCellView: CellView = memo(({value}: {value: Value}) => {
  if (!isNumberData(value.data)) return null;

  return (
    <div className="AknGrid-bodyCellContainer" title={numberDataStringValue(value.data)}>
      {formatNumber(Number(numberDataStringValue(value.data)))}
    </div>
  );
});

const formatNumber = (number: number): string => new Intl.NumberFormat(uiLocaleTag()).format(number);
const uiLocaleTag = (): string => UserContext.get('uiLocale').replace('_', '-');

export const cell = NumberCellView;
