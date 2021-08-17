import React from 'react';
import {getSelectOption, getSelectOptions} from '../repositories/SelectOption';
import {ColumnCode, SelectOption, TableConfiguration} from '../models/TableConfiguration';
import {getLabel, useRouter, useUserContext} from '@akeneo-pim-community/shared';
import {Attribute} from '../models/Attribute';
import {TableRowWithId} from './TableFieldApp';
import {TableCell} from '../models/TableValue';

const useFetchOptions: (
  tableConfiguration: TableConfiguration,
  attribute: Attribute,
  valueData: TableRowWithId[]
) => {
  getOptionsFromColumnCode: (columnCode: ColumnCode) => SelectOption[] | undefined;
  getOptionLabel: (columnCode: ColumnCode, value: TableCell) => string | undefined | null;
} = (tableConfiguration, attribute, valueData) => {
  const router = useRouter();
  const userContext = useUserContext();

  const [options, setOptions] = React.useState<{[columnCode: string]: SelectOption[]}>({});
  const [selectOptionLabels, setSelectOptionLabels] = React.useState<{[key: string]: string | null}>({});
  const firstColumn = tableConfiguration[0];

  const innerGetOptionLabel = async (columnCode: ColumnCode, value: string) => {
    const selectOption = await getSelectOption(router, attribute.code, columnCode, value);

    return selectOption ? getLabel(selectOption.labels, userContext.get('catalogLocale'), selectOption.code) : null;
  };

  React.useEffect(() => {
    const f = async () => {
      for await (const column of tableConfiguration.filter(
        columnDefinition => columnDefinition.data_type === 'select'
      )) {
        options[column.code] = (await getSelectOptions(router, attribute.code, column.code)) || [];
      }
      setOptions({...options});

      for await (const row of valueData) {
        selectOptionLabels[`${firstColumn.code}-${row[firstColumn.code]}`] = await innerGetOptionLabel(
          firstColumn.code,
          row[firstColumn.code] as string
        );
      }
      setSelectOptionLabels({...selectOptionLabels});
    };
    f();
  }, [valueData.length]);

  const getOptionsFromColumnCode = (columnCode: ColumnCode) => {
    return options[columnCode];
  };

  const getOptionLabel = (columnCode: ColumnCode, value: TableCell) => {
    return selectOptionLabels[`${columnCode}-${value}`];
  };

  return {
    getOptionsFromColumnCode,
    getOptionLabel,
  };
};

export {useFetchOptions};
