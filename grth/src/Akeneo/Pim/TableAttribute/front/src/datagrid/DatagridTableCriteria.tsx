import {ColumnCode, ColumnDefinition, isFilterValid, PendingTableFilterValue} from '../models';
import {getLabel, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import React from 'react';
import {ValuesFilterMapping} from './FilterValues';

type DatagridTableCriteriaProps = {
  filterValue?: PendingTableFilterValue;
};

export const DatagridTableCriteria: React.FC<DatagridTableCriteriaProps> = ({filterValue}) => {
  const userContext = useUserContext();
  const translate = useTranslate();
  const catalogLocale = userContext.get('catalogLocale');

  const valueRenderers: {[dataType: string]: {[operator: string]: (value: any, columnCode: ColumnCode) => string}} = {};
  Object.keys(ValuesFilterMapping).forEach(dataType => {
    valueRenderers[dataType] = {};
    Object.keys(ValuesFilterMapping[dataType]).forEach(operator => {
      valueRenderers[dataType][operator] = ValuesFilterMapping[dataType][operator].useValueRenderer();
    });
  });

  let criteriaHint = translate('pim_common.all');
  if (filterValue && isFilterValid(filterValue)) {
    criteriaHint = '';
    criteriaHint +=
      typeof filterValue.row === 'undefined' || filterValue.row === null
        ? translate('pim_table_attribute.datagrid.any') + ' '
        : getLabel(filterValue.row.labels, catalogLocale, filterValue.row.code) + ' ';
    criteriaHint +=
      getLabel(
        (filterValue.column as ColumnDefinition).labels,
        catalogLocale,
        (filterValue.column as ColumnDefinition).code
      ) + ' ';
    criteriaHint += translate(`pim_common.operators.${filterValue.operator}`) + ' ';

    const valueRenderer = (valueRenderers[(filterValue.column as ColumnDefinition).data_type || ''] || {})[
      filterValue.operator || ''
    ];
    if (valueRenderer) {
      criteriaHint += valueRenderer(filterValue.value, (filterValue.column as ColumnDefinition).code);
    }
  }

  return (
    <span className='AknFilterBox-filterCriteria AknFilterBox-filterCriteria--limited' title={criteriaHint}>
      {criteriaHint}
    </span>
  );
};
