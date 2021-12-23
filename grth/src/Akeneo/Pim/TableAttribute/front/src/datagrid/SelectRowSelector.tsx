import React from 'react';
import {getLabel, useUserContext} from '@akeneo-pim-community/shared';
import {ReferenceEntityRecord, SelectOption} from '../models';
import {useFetchOptions} from '../product';
import {useAttributeContext} from '../contexts';
import {RowSelectorSelectInput} from './RowSelectorSelectInput';

type SelectRowSelectorProps = {
  onChange: (value?: ReferenceEntityRecord | SelectOption | null) => void;
  /**
   * If value is:
   * - undefined: the placeholder should be displayed
   * - null: the user has selected 'Any row'
   * - a SelectOption: the user has selected a row
   */
  value?: SelectOption | null;
  anyRowOption: SelectOption;
};

const SelectRowSelector: React.FC<SelectRowSelectorProps> = ({onChange, value, anyRowOption}) => {
  const catalogLocale = useUserContext().get('catalogLocale');
  const {attribute, setAttribute} = useAttributeContext();
  const {getOptionsFromColumnCode} = useFetchOptions(attribute, setAttribute);
  const options = attribute ? getOptionsFromColumnCode(attribute.table_configuration[0].code) : [];
  const [page, setPage] = React.useState<number>(0);
  const [searchValue, setSearchValue] = React.useState<string>('');

  const filteredOptions = [anyRowOption]
    .concat(options || [])
    .filter(option => {
      return (
        option.code.toLowerCase().includes(searchValue.toLowerCase()) ||
        getLabel(option.labels, catalogLocale, option.code).toLowerCase().includes(searchValue.toLowerCase())
      );
    })
    .slice(0, (page + 1) * 20);

  const handleNextPage = React.useCallback(() => {
    setPage(page + 1);
  }, []);

  return (
    <RowSelectorSelectInput
      onChange={onChange}
      onNextPage={handleNextPage}
      value={value}
      options={filteredOptions}
      setSearchValue={setSearchValue}
    />
  );
};

export {SelectRowSelector};
