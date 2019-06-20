import * as React from 'react';
import {NormalizedValue} from 'akeneoassetmanager/domain/model/asset/value';
import {CellView} from 'akeneoassetmanager/application/configuration/value';

const UserContext = require('pim/user-context');
const memo = (React as any).memo;

const NumberCellView: CellView = memo(({value}: {value: NormalizedValue}) => {
  const number = undefined === value ? '' : value.data;

  return (
    <div className="AknGrid-bodyCellContainer" title={number}>
      {formatNumber(number)}
    </div>
  );
});

const formatNumber = (number: number): string => new Intl.NumberFormat(uiLocaleTag()).format(number);
const uiLocaleTag = (): string => UserContext.get('uiLocale').replace('_', '-');

export const cell = NumberCellView;
