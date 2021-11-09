import React, {useState} from 'react';
import {
  AddingValueIllustration,
  ArrowDownIcon,
  Button,
  Checkbox,
  Dropdown,
  Search,
  useBooleanState,
} from 'akeneo-design-system';
import {getLabel, useSecurity, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {ColumnCode, SelectOptionCode} from '../models';
import styled from 'styled-components';
import {CenteredHelper} from '../shared';
import {useAttributeContext} from '../contexts';
import {useFetchOptions} from './useFetchOptions';
import {useManageOptions} from './useManageOptions';

const BATCH_SIZE = 20;

type AddRowsButtonProps = {
  columnCode: ColumnCode;
  checkedOptionCodes: SelectOptionCode[];
  toggleChange: (optionCode: SelectOptionCode) => void;
  maxRowCount?: number;
};

const EditOptionsContainer = styled.div`
  margin: 10px;
  text-align: center;
`;

type Option = {
  code: string;
  label: string;
};

const AddRowsButton: React.FC<AddRowsButtonProps> = ({
  columnCode,
  checkedOptionCodes,
  toggleChange,
  maxRowCount = 100,
}) => {
  const translate = useTranslate();
  const security = useSecurity();
  const userContext = useUserContext();

  const {attribute, setAttribute} = useAttributeContext();
  const {ManageOptionsModal, openManageOptions} = useManageOptions(columnCode);

  const [isOpen, open, close] = useBooleanState(false);
  const [searchValue, setSearchValue] = useState('');
  const [numberOfDisplayedItems, setNumberOfDisplayedItems] = useState<number>(BATCH_SIZE);
  const {getOptionsFromColumnCode} = useFetchOptions(attribute, setAttribute);
  const hasEditPermission = security.isGranted('pim_enrich_attribute_edit');
  const lowercaseCheckedOptionCodes = checkedOptionCodes.map(code => code.toLowerCase());

  const searchRef = React.createRef<HTMLInputElement>();

  const items = (getOptionsFromColumnCode(columnCode) || []).map(option => {
    return {
      code: option.code,
      label: getLabel(option.labels, userContext.get('catalogLocale'), option.code),
    };
  });

  const focus = (ref: React.RefObject<HTMLInputElement>) => {
    ref.current?.focus();
  };

  React.useEffect(() => {
    if (isOpen) {
      focus(searchRef);
    }
  }, [isOpen]);

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
      return item.label.toLowerCase().includes(searchValue.toLowerCase());
    })
    .slice(0, numberOfDisplayedItems);

  return (
    <>
      <Dropdown>
        <Button onClick={open} level='secondary' size='small' ghost>
          {translate('pim_table_attribute.product_edit_form.add_rows')}
          <ArrowDownIcon />
        </Button>
        {isOpen && attribute && (
          <Dropdown.Overlay horizontalPosition='left' onClose={close}>
            {(searchValue !== '' || itemsToDisplay.length > 0) && (
              <Dropdown.Header>
                <Search
                  inputRef={searchRef}
                  onSearchChange={handleSearchValue}
                  placeholder={translate('pim_table_attribute.product_edit_form.search')}
                  searchValue={searchValue}
                  title={translate('pim_table_attribute.product_edit_form.search')}
                />
              </Dropdown.Header>
            )}
            <Dropdown.ItemCollection onNextPage={handleNextPage} data-testid={'item_collection'}>
              {itemsToDisplay.map((item, index) => (
                <Dropdown.Item
                  key={item.code}
                  disabled={!checkedOptionCodes.includes(item.code) && checkedOptionCodes.length >= maxRowCount}
                >
                  <Checkbox
                    checked={lowercaseCheckedOptionCodes.includes(item.code.toLowerCase())}
                    onChange={() => toggleChange(item.code)}
                    data-testid={`checkbox-${index}`}
                  >
                    {item.label}
                  </Checkbox>
                </Dropdown.Item>
              ))}
              {searchValue === '' && itemsToDisplay.length === 0 && (
                <CenteredHelper.Container>
                  <CenteredHelper illustration={<AddingValueIllustration />}>
                    <CenteredHelper.Title>
                      {translate('pim_table_attribute.form.product.no_options')}
                    </CenteredHelper.Title>
                    {!hasEditPermission &&
                      translate('pim_table_attribute.form.product.no_add_options_unallowed', {
                        attributeLabel: getLabel(attribute.labels, userContext.get('catalogLocale'), attribute.code),
                      })}
                  </CenteredHelper>
                </CenteredHelper.Container>
              )}
              {searchValue !== '' && itemsToDisplay.length === 0 && (
                <CenteredHelper.Container>
                  <CenteredHelper illustration={<AddingValueIllustration />}>
                    <CenteredHelper.Title>
                      <CenteredHelper.Title>
                        {translate('pim_table_attribute.form.product.no_results')}
                      </CenteredHelper.Title>
                    </CenteredHelper.Title>
                  </CenteredHelper>
                </CenteredHelper.Container>
              )}
            </Dropdown.ItemCollection>
            {hasEditPermission && (
              <EditOptionsContainer>
                <Button
                  onClick={() => {
                    close();
                    openManageOptions();
                  }}
                  ghost
                  level='secondary'
                >
                  {translate('pim_table_attribute.form.attribute.manage_options')}
                </Button>
              </EditOptionsContainer>
            )}
          </Dropdown.Overlay>
        )}
      </Dropdown>
      <ManageOptionsModal />
    </>
  );
};

export {AddRowsButton};
