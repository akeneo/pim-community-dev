import {
  BackendTableFilterValue,
  ColumnDefinition,
  ReferenceEntityColumnDefinition,
  ReferenceEntityRecord,
  SelectColumnDefinition,
  SelectOption,
} from '../models';
import {getLabel, useRouter, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import React from 'react';
import {ValuesFilterMapping} from './FilterValues';
import {useAttributeContext} from '../contexts';
import {useFetchOptions} from '../product';
import {ReferenceEntityRecordRepository} from '../repositories';

type DatagridTableCriteriaProps = {
  filterValue: BackendTableFilterValue;
};

export const DatagridTableCriteria: React.FC<DatagridTableCriteriaProps> = ({filterValue}) => {
  const router = useRouter();
  const userContext = useUserContext();
  const translate = useTranslate();
  const {attribute, setAttribute} = useAttributeContext();
  const {getOptionsFromColumnCode} = useFetchOptions(attribute, setAttribute);
  const catalogLocale = userContext.get('catalogLocale');
  const [row, setRow] = React.useState<SelectOption | ReferenceEntityRecord | undefined>();

  const valueRenderers: {[dataType: string]: {[operator: string]: string | null}} = {};
  Object.keys(ValuesFilterMapping).forEach(dataType => {
    valueRenderers[dataType] = {};
    Object.keys(ValuesFilterMapping[dataType]).forEach(operator => {
      valueRenderers[dataType][operator] = ValuesFilterMapping[dataType][operator].useValueRenderer(
        filterValue.value,
        filterValue.column
      );
    });
  });

  const column: ColumnDefinition | undefined = attribute?.table_configuration?.find(
    ({code}) => code === filterValue.column
  );
  const firstColumn: SelectColumnDefinition | ReferenceEntityColumnDefinition | undefined = attribute
    ?.table_configuration[0] as SelectColumnDefinition | ReferenceEntityColumnDefinition | undefined;

  React.useEffect(() => {
    if (attribute) {
      if (typeof filterValue.row === 'undefined' || filterValue.row === null || typeof firstColumn === 'undefined') {
        setRow(undefined);
      } else if (firstColumn.data_type === 'select') {
        setRow(getOptionsFromColumnCode(firstColumn.code)?.find(({code}) => code === filterValue.row));
      } else if (firstColumn.data_type === 'reference_entity') {
        ReferenceEntityRecordRepository.findByCode(
          router,
          firstColumn.reference_entity_identifier,
          filterValue.row
        ).then(record => setRow(record || undefined));
      }
    }
  }, [attribute, column, filterValue, getOptionsFromColumnCode, router, filterValue.row]);

  let criteriaHint = translate('pim_common.all');
  if (typeof filterValue.operator !== 'undefined' && typeof filterValue.column !== 'undefined') {
    criteriaHint = '';
    criteriaHint +=
      typeof filterValue.row === 'undefined' || filterValue.row === null
        ? translate('pim_table_attribute.datagrid.any') + ' '
        : getLabel(row?.labels || {}, catalogLocale, filterValue.row) + ' ';
    criteriaHint += getLabel(column?.labels || {}, catalogLocale, filterValue.column) + ' ';
    criteriaHint += translate(`pim_common.operators.${filterValue.operator}`) + ' ';

    const valueRenderer = (valueRenderers[column?.data_type || ''] || {})[filterValue.operator || ''];
    if (valueRenderer) {
      criteriaHint += valueRenderer;
    }
  }

  return (
    <span className='AknFilterBox-filterCriteria AknFilterBox-filterCriteria--limited' title={criteriaHint}>
      {criteriaHint}
    </span>
  );
};
