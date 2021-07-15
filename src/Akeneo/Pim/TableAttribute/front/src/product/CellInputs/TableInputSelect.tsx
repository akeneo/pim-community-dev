import React, {useState} from 'react';
import {Dropdown, TableInput, AddingValueIllustration} from 'akeneo-design-system';
import {SelectOption, SelectOptionCode} from '../../models/TableConfiguration';
import {getLabel, LoadingPlaceholderContainer, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import styled from 'styled-components';

const BATCH_SIZE = 20;

type TableInputSelectProps = {
  value?: SelectOptionCode;
  onChange: (value: SelectOptionCode | undefined) => void;
  options?: SelectOption[];
  inError?: boolean;
  readOnly?: boolean;
};

const FakeInput = styled.div`
  margin: 0 10px;
`;

const CenteredHelper = styled.div`
  text-align: center;
  & > * {
    display: block;
    margin: auto;
  }
`;

const TableInputSelect: React.FC<TableInputSelectProps> = ({value, onChange, options, readOnly = false, inError = false, ...rest}) => {
  const translate = useTranslate();
  const userContext = useUserContext();

  const [searchValue, setSearchValue] = React.useState<string>('');
  const [numberOfDisplayedItems, setNumberOfDisplayedItems] = useState<number>(BATCH_SIZE);

  const isLoading = typeof options === 'undefined';
  let option = null;
  if (value && typeof options !== 'undefined') {
    option = options.find(option => option.code === value);
  }
  let label = '';
  if (value && option) {
    label = getLabel(option.labels, userContext.get('catalogLocale'), option.code);
  } else if (value) {
    label = `[${value}]`;
  }
  const notFoundOption = typeof option === 'undefined';

  const handleClear = () => {
    onChange(undefined);
  };

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

  if (isLoading) {
    return (
      <LoadingPlaceholderContainer>
        <FakeInput>{translate('pim_common.loading')}</FakeInput>
      </LoadingPlaceholderContainer>
    );
  }

  return (
    <TableInput.Select
      value={`${label}${readOnly ? ' [read only]' : ''}`}
      onClear={handleClear}
      clearLabel={translate('pim_common.clear')}
      openDropdownLabel={translate('pim_common.open')}
      searchPlaceholder={translate('pim_common.search')}
      searchTitle={translate('pim_common.search')}
      onNextPage={handleNextPage}
      searchValue={searchValue}
      onSearchChange={handleSearchValue}
      inError={inError || notFoundOption}
      /** TODO Implement this **/
      //readOnly={readOnly}
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
