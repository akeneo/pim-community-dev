import React from 'react';
import {ColumnCode, SelectColumnDefinition, SelectOption, TableAttribute} from '../models';
import {getLabel, useRouter} from '@akeneo-pim-community/shared';
import {SelectOptionRepository} from '../repositories';
import {useIsMounted} from '../shared';
import {useLocaleCode} from '../contexts';

const useFetchOptions: (
  attribute: TableAttribute | undefined,
  setAttribute: (tableAttribute: TableAttribute) => void
) => {
  getOptionsFromColumnCode: (columnCode: ColumnCode) => SelectOption[] | undefined;
  getOptionLabel: (columnCode: ColumnCode, value: string) => string | undefined | null;
} = (attribute, setAttribute) => {
  const router = useRouter();
  const isMounted = useIsMounted();
  const localeCode = useLocaleCode();

  React.useEffect(() => {
    if (attribute) {
      const f = async () => {
        let dirty = false;
        for await (const column of attribute.table_configuration.filter(
          columnDefinition => columnDefinition.data_type === 'select'
        )) {
          const i = attribute.table_configuration.findIndex(columnDefinition => columnDefinition.code === column.code);
          const currentOptions = (attribute.table_configuration[i] as SelectColumnDefinition).options;
          const newOptions = (await SelectOptionRepository.findFromColumn(router, attribute.code, column.code)) || [];
          if (JSON.stringify(currentOptions) !== JSON.stringify(newOptions)) {
            dirty = true;
            (attribute.table_configuration[i] as SelectColumnDefinition).options = newOptions;
          }
        }
        if (isMounted() && dirty) setAttribute({...attribute});
      };
      f();
    }
  }, [attribute]);

  const getOptionsFromColumnCode: (columnCode: ColumnCode) => SelectOption[] | undefined = columnCode => {
    if (!attribute) return undefined;
    const i = attribute?.table_configuration.findIndex(columnDefinition => columnDefinition.code === columnCode);
    return (attribute?.table_configuration[i] as SelectColumnDefinition | undefined)?.options;
  };

  /**
   * @returns
   *   - undefined: attribute or option is not fetched
   *   - null: the option is not found
   *   - string: the label of the found
   * @param value
   */
  const getOptionLabel = (columnCode: ColumnCode, value: string) => {
    if (!attribute) return undefined;
    const options = getOptionsFromColumnCode(columnCode);
    if (typeof options === 'undefined') return undefined;
    const option = options.find((option: SelectOption) => option.code === value);
    return option ? getLabel(option.labels, localeCode, option.code) : null;
  };

  return {
    getOptionsFromColumnCode,
    getOptionLabel,
  };
};

export {useFetchOptions};
