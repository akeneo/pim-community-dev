import React from 'react';
import {getSelectOption, getSelectOptions} from '../repositories/SelectOption';
import {ColumnCode, SelectOption, TableConfiguration} from '../models/TableConfiguration';
import {getLabel, useRouter, useUserContext} from '@akeneo-pim-community/shared';
import {TableCell, TableRow} from '../models/TableValue';

const useFetchOptions: (
  tableConfiguration: TableConfiguration | undefined,
  attributeCode: string,
  valueData: TableRow[]
) => {
  getOptionsFromColumnCode: (columnCode: ColumnCode) => SelectOption[] | undefined;
  getOptionLabel: (columnCode: ColumnCode, value: TableCell) => string | undefined | null;
} = (tableConfiguration, attributeCode, valueData) => {
  const router = useRouter();
  const userContext = useUserContext();

  const [options, setOptions] = React.useState<{[columnCode: string]: SelectOption[]}>({});
  const [selectOptionLabels, setSelectOptionLabels] = React.useState<{[key: string]: string | null}>({});

  const innerGetOptionLabel = async (columnCode: ColumnCode, value: string) => {
    const selectOption = await getSelectOption(router, attributeCode, columnCode, value);

    return selectOption ? getLabel(selectOption.labels, userContext.get('catalogLocale'), selectOption.code) : null;
  };

  React.useEffect(() => {
    if (tableConfiguration) {
      const f = async () => {
        for await (const column of tableConfiguration.filter(
          columnDefinition => columnDefinition.data_type === 'select'
        )) {
          options[column.code] = (await getSelectOptions(router, attributeCode, column.code)) || [];
          for await (const row of valueData) {
            selectOptionLabels[`${column.code}-${row[column.code]}`] = await innerGetOptionLabel(
              column.code,
              row[column.code] as string
            );
          }
        }
        setOptions({...options});
        setSelectOptionLabels({...selectOptionLabels});
      };
      f();
    }
  }, [valueData.length, tableConfiguration]);

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
