import React, {useState} from 'react';
import {AddingValueIllustration, Button, Dropdown, Placeholder, TableInput} from 'akeneo-design-system';
import {ColumnCode, SelectOption, SelectOptionCode, TableAttribute} from '../../models';
import {getLabel, useSecurity, useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {LoadingPlaceholderContainer} from '../../shared';
import {useFetchOptions} from '../useFetchOptions';
import {CellInput} from './index';
import {useManageOptions} from '../useManageOptions';
import {useLocaleCode} from '../../contexts';

const BATCH_SIZE = 20;

type TableInputSelectProps = {
  value?: SelectOptionCode;
  onChange: (value: SelectOptionCode | undefined) => void;
  inError?: boolean;
  highlighted?: boolean;
  columnCode: ColumnCode;
  attribute: TableAttribute;
  setAttribute: (tableAttribute: TableAttribute) => void;
};

const FakeInput = styled.div`
  margin: 0 10px;
  height: 20px;
`;

const EditOptionsContainer = styled.div`
  margin: 10px;
  text-align: center;
`;

const SelectInput: React.FC<TableInputSelectProps> = ({
  value,
  onChange,
  inError = false,
  highlighted = false,
  columnCode,
  attribute,
  setAttribute,
  ...rest
}) => {
  const translate = useTranslate();
  const security = useSecurity();
  const localeCode = useLocaleCode();

  const [searchValue, setSearchValue] = React.useState<string>('');
  const [numberOfDisplayedItems, setNumberOfDisplayedItems] = useState<number>(BATCH_SIZE);
  const [closeTick, setCloseTick] = React.useState<boolean>(false);
  const {ManageOptionsModal, openManageOptions} = useManageOptions(columnCode);

  const hasEditPermission = security.isGranted('pim_enrich_attribute_edit');

  const {getOptionsFromColumnCode} = useFetchOptions(attribute, setAttribute);
  const options = getOptionsFromColumnCode(columnCode);

  let option = null;
  if (value && typeof options !== 'undefined') {
    option = options.find(option => option.code.toLowerCase() === value.toLowerCase());
  }
  let label = '';
  if (value && option) {
    label = getLabel(option.labels, localeCode, option.code);
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
      return (item.labels[localeCode] || item.code).includes(searchValue);
    })
    .slice(0, numberOfDisplayedItems);

  if (!attribute || typeof options === 'undefined') {
    return (
      <LoadingPlaceholderContainer>
        <FakeInput>{translate('pim_common.loading')}</FakeInput>
      </LoadingPlaceholderContainer>
    );
  }

  let BottomHelper = undefined;
  if (searchValue === '' && itemsToDisplay.length === 0) {
    BottomHelper = (
      <Placeholder
        illustration={<AddingValueIllustration />}
        title={translate('pim_table_attribute.form.product.no_options')}>
        {!hasEditPermission &&
          translate('pim_table_attribute.form.product.no_add_options_unallowed', {
            attributeLabel: getLabel(attribute.labels, localeCode, attribute.code),
          })}
      </Placeholder>
    );
  } else if (searchValue !== '' && itemsToDisplay.length === 0) {
    BottomHelper = (
      <Placeholder
        illustration={<AddingValueIllustration />}
        title={translate('pim_table_attribute.form.product.no_results')}
      />
    );
  }
  if (hasEditPermission) {
    BottomHelper = (
      <>
        {BottomHelper}
        <EditOptionsContainer>
          <Button
            onClick={() => {
              setCloseTick(!closeTick);
              openManageOptions();
            }}
            ghost
            level='secondary'>
            {translate('pim_table_attribute.form.attribute.manage_options')}
          </Button>
        </EditOptionsContainer>
      </>
    );
  }

  return (
    <>
      <TableInput.Select
        highlighted={highlighted}
        value={label}
        onClear={handleClear}
        clearLabel={translate('pim_common.clear')}
        openDropdownLabel={translate('pim_common.open')}
        searchPlaceholder={translate('pim_common.search')}
        searchTitle={translate('pim_common.search')}
        onNextPage={handleNextPage}
        searchValue={searchValue}
        onSearchChange={handleSearchValue}
        inError={inError || notFoundOption}
        closeTick={closeTick}
        bottomHelper={BottomHelper}
        withSearch={searchValue !== '' || itemsToDisplay.length > 0}
        {...rest}>
        {itemsToDisplay.map(option => {
          return (
            <Dropdown.Item key={option.code} onClick={() => onChange(option.code)}>
              {getLabel(option.labels, localeCode, option.code)}
            </Dropdown.Item>
          );
        })}
      </TableInput.Select>
      <ManageOptionsModal />
    </>
  );
};

const renderer: CellInput = ({
  row,
  columnDefinition,
  onChange,
  inError,
  highlighted,
  attribute,
  setAttribute,
  ...rest
}) => {
  const cell = row[columnDefinition.code] as SelectOptionCode | undefined;

  return (
    <SelectInput
      highlighted={highlighted}
      value={cell}
      onChange={onChange}
      inError={inError}
      columnCode={columnDefinition.code}
      attribute={attribute}
      setAttribute={setAttribute}
      {...rest}
    />
  );
};

export default renderer;
