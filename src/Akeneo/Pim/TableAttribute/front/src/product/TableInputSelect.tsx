import React, {useState} from 'react';
import {Dropdown, TableInput, AddingValueIllustration} from 'akeneo-design-system';
import {SelectOption, SelectOptionCode} from '../models/TableConfiguration';
import {getLabel, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import styled from 'styled-components';

const BATCH_SIZE = 20;

type TableInputSelectProps = {
  value?: SelectOptionCode;
  onChange: (value: SelectOptionCode | undefined) => void;
  options?: SelectOption[];
};

const CenteredHelper = styled.div`
  text-align: center;
  & > * {
    display: block;
    margin: auto;
  }
`;

const TableInputSelect: React.FC<TableInputSelectProps> = ({value, onChange, options, ...rest}) => {
  const translate = useTranslate();
  const userContext = useUserContext();

  const [searchValue, setSearchValue] = React.useState<string>('');
  const [numberOfDisplayedItems, setNumberOfDisplayedItems] = useState<number>(BATCH_SIZE);

  const handleClear = () => {
    onChange(undefined);
  };

  if (!options) {
    return <div>Loading... TODO</div>;
  }

  let label = '';
  if (value) {
    const option = options.find(option => option.code === value);
    label = option ? getLabel(option.labels, userContext.get('catalogLocale'), option.code) : `[${value}]`;
  }

  const handleNextPage = () => {
    setNumberOfDisplayedItems(numberOfDisplayedItems + BATCH_SIZE);
  };

  const handleSearchValue = (searchValue: string) => {
    setSearchValue(searchValue);
    setNumberOfDisplayedItems(BATCH_SIZE);
  };

  const itemsToDisplay = (options || [])
    .filter((item: SelectOption) => {
      if (searchValue === '') {
        return true;
      }
      return (item.labels[userContext.get('catalogLocale')] || item.code).includes(searchValue);
    })
    .slice(0, numberOfDisplayedItems);

  return (
    <TableInput.Select
      value={label}
      onClear={handleClear}
      clearLabel={translate('pim_common.clear')}
      openDropdownLabel={translate('pim_common.open')}
      searchPlaceholder={translate('pim_common.search')}
      searchTitle={translate('pim_common.search')}
      onNextPage={handleNextPage}
      searchValue={searchValue}
      onSearchChange={handleSearchValue}
      {...rest}>
      {itemsToDisplay.map(option => {
        return (
          <Dropdown.Item key={option.code} onClick={() => onChange(option.code)}>
            {getLabel(option.labels, userContext.get('catalogLocale'), option.code)}
          </Dropdown.Item>
        );
      })}
      {searchValue === '' && itemsToDisplay.length === 0 && (
        <CenteredHelper>
          <AddingValueIllustration size={100} />
          No options. Add options ! TODO
        </CenteredHelper>
      )}
      {searchValue !== '' && itemsToDisplay.length === 0 && (
        <CenteredHelper>
          <AddingValueIllustration size={100} />
          No options. Change search TODO
        </CenteredHelper>
      )}
    </TableInput.Select>
  );
};

export {TableInputSelect};
