import React from 'react';
import {getLabel, useUserContext} from '@akeneo-pim-community/shared';
import {SelectOption, SelectOptionCode} from '../models';
import {useFetchOptions} from '../product';
import {useAttributeContext} from '../contexts';
import {RowSelectorSelectInput} from './RowSelectorSelectInput';

type SelectRowSelectorProps = {
  onChange: (value?: SelectOptionCode | null) => void;
  /**
   * If value is:
   * - undefined: the placeholder should be displayed
   * - null: the user has selected 'Any row'
   * - a SelectOption: the user has selected a row
   */
  value?: SelectOptionCode | null;
};

const SelectRowSelector: React.FC<SelectRowSelectorProps> = ({onChange, value}) => {
  const catalogLocale = useUserContext().get('catalogLocale');
  const {attribute, setAttribute} = useAttributeContext();
  const {getOptionsFromColumnCode} = useFetchOptions(attribute, setAttribute);
  const options = attribute ? getOptionsFromColumnCode(attribute.table_configuration[0].code) : [];
  const [page, setPage] = React.useState<number>(0);
  const [searchValue, setSearchValue] = React.useState<string>('');

  const filteredOptions = (options || [])
    .filter(option => {
      return (
        option.code.toLowerCase().includes(searchValue.toLowerCase()) ||
        getLabel(option.labels, catalogLocale, option.code)
          .toLowerCase()
          .includes(searchValue.toLowerCase())
      );
    })
    .slice(0, (page + 1) * 20);

  const handleNextPage = React.useCallback(() => {
    setPage(page + 1);
  }, []);

  return (
    <RowSelectorSelectInput<SelectOption>
      onChange={onChange}
      onNextPage={handleNextPage}
      value={value}
      options={filteredOptions}
      setSearchValue={setSearchValue}
    />
  );
};

export {SelectRowSelector};
