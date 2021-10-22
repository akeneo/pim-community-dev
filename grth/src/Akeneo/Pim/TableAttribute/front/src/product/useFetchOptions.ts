import React from 'react';
import {AttributeCode, ColumnCode, SelectOption, TableCell, TableConfiguration, TableRow} from '../models';
import {getLabel, useRouter, useUserContext} from '@akeneo-pim-community/shared';
import {SelectOptionRepository} from '../repositories';
import {useIsMounted} from '../shared';

const useFetchOptions: (
  tableConfiguration: TableConfiguration | undefined,
  attributeCode: AttributeCode,
  valueData: TableRow[]
) => {
  getOptionsFromColumnCode: (columnCode: ColumnCode) => SelectOption[] | undefined;
  getOptionLabel: (columnCode: ColumnCode, value: TableCell) => string | undefined | null;
} = (tableConfiguration, attributeCode, valueData) => {
  const router = useRouter();
  const userContext = useUserContext();
  const isMounted = useIsMounted();

  const [options, setOptions] = React.useState<{[columnCode: string]: SelectOption[]}>({});
  const [selectOptionLabels, setSelectOptionLabels] = React.useState<{[key: string]: string | null}>({});

  const innerGetOptionLabel = async (columnCode: ColumnCode, value: string) => {
    const selectOption = await SelectOptionRepository.findFromCell(router, attributeCode, columnCode, value);

    return selectOption ? getLabel(selectOption.labels, userContext.get('catalogLocale'), selectOption.code) : null;
  };

  React.useEffect(() => {
    if (tableConfiguration) {
      const f = async () => {
        for await (const column of tableConfiguration.filter(
          columnDefinition => columnDefinition.data_type === 'select'
        )) {
          options[column.code] =
            (await SelectOptionRepository.findFromColumn(router, attributeCode, column.code)) || [];
          for await (const row of valueData) {
            if (typeof row[column.code] !== 'undefined') {
              selectOptionLabels[`${column.code}-${row[column.code]}`.toLowerCase()] = await innerGetOptionLabel(
                column.code,
                row[column.code] as string
              );
            }
          }
        }
        if (!isMounted()) return;
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
    return selectOptionLabels[`${columnCode}-${value}`.toLowerCase()];
  };

  return {
    getOptionsFromColumnCode,
    getOptionLabel,
  };
};

export {useFetchOptions};
