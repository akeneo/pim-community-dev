import React, {useState} from 'react';
import {
  useBooleanState,
  Dropdown,
  ArrowDownIcon,
  Search,
  Button,
  Checkbox,
  AddingValueIllustration
} from 'akeneo-design-system';
import {useRouter, getLabel, useUserContext, useTranslate} from '@akeneo-pim-community/shared';
import {ColumnCode, SelectOption, SelectOptionCode} from '../models/TableConfiguration';
import {getSelectOptions} from '../repositories/SelectOption';
import styled from "styled-components";

const BATCH_SIZE = 20;

type AddRowsButtonProps = {
  attributeCode: string;
  columnCode: ColumnCode;
  checkedOptionCodes: SelectOptionCode[];
  toggleChange: (optionCode: SelectOptionCode) => void;
};

const CenteredHelper = styled.div`
  text-align: center;
  & > * {
    display: block;
    margin: auto;
  } 
`;

type Option = {
  code: string;
  label: string;
};

const AddRowsButton: React.FC<AddRowsButtonProps> = ({attributeCode, columnCode, checkedOptionCodes, toggleChange}) => {
  const router = useRouter();
  const translate = useTranslate();
  const [isOpen, open, close] = useBooleanState(false);
  const [searchValue, setSearchValue] = useState('');
  const [items, setItems] = useState<Option[] | undefined>(undefined);
  const [numberOfDisplayedItems, setNumberOfDisplayedItems] = useState<number>(BATCH_SIZE);
  const userContext = useUserContext();

  React.useEffect(() => {
    if (isOpen && typeof items === 'undefined') {
      getSelectOptions(router, attributeCode, columnCode).then(selectOptions => {
        setItems(
          (selectOptions ?? []).map((option: SelectOption) => {
            return {
              code: option.code,
              label: getLabel(option.labels, userContext.get('catalogLocale'), option.code),
              checked: false,
            };
          })
        );
      });
    }
  }, [router, isOpen]);

  const handleNextPage = () => {
    setNumberOfDisplayedItems(numberOfDisplayedItems + BATCH_SIZE);
  };

  const handleSearchValue = (searchValue: string) => {
    setSearchValue(searchValue);
    setNumberOfDisplayedItems(BATCH_SIZE);
  };

  const itemsToDisplay = (items || [])
    .filter((item: Option) => {
      if (searchValue === '') {
        return true;
      }
      return item.label.includes(searchValue);
    })
    .slice(0, numberOfDisplayedItems);

  return (
    <Dropdown>
      <Button onClick={open}>
        {translate('pim_table_attribute.product_edit_form.add_rows')}
        <ArrowDownIcon />
      </Button>
      {isOpen && (
        <Dropdown.Overlay verticalPosition='down' onClose={close}>
          <Dropdown.Header>
            {items && items.length > 0 && <Search
              onSearchChange={handleSearchValue}
              placeholder={translate('pim_table_attribute.product_edit_form.search')}
              searchValue={searchValue}
              title={translate('pim_table_attribute.product_edit_form.search')}
            />}
          </Dropdown.Header>
          <Dropdown.ItemCollection onNextPage={handleNextPage} data-testid={'item_collection'}>
            {itemsToDisplay.map((item, index) => (
              <Dropdown.Item key={item.code}>
                <Checkbox
                  checked={checkedOptionCodes.includes(item.code)}
                  onChange={() => toggleChange(item.code)}
                  data-testid={`checkbox-${index}`}>
                  {item.label}
                </Checkbox>
              </Dropdown.Item>
            ))}
            {searchValue === '' && itemsToDisplay.length === 0 &&
            <CenteredHelper>
              <AddingValueIllustration size={100}/>
              No options. Add options ! TODO
            </CenteredHelper>
            }
            {searchValue !== '' && itemsToDisplay.length === 0 &&
            <CenteredHelper>
              <AddingValueIllustration size={100}/>
              No options. Change search TODO
            </CenteredHelper>
            }
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export {AddRowsButton};
