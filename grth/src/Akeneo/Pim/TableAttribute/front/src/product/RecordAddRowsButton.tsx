import React, {useCallback, useEffect, useMemo, useState} from 'react';
import styled from 'styled-components';
import {
  AddingValueIllustration,
  ArrowDownIcon,
  Button,
  Checkbox,
  Dropdown,
  getColor,
  LoaderIcon,
  Placeholder,
  Search,
  useBooleanState,
  useDebounce,
} from 'akeneo-design-system';
import {getLabel, useSecurity, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {castReferenceEntityColumnDefinition, SelectOptionCode} from '../models';
import {useAttributeContext} from '../contexts';
import {RECORD_FETCHER_DEFAULT_LIMIT} from '../fetchers';
import {useRecords} from './useRecords';
import {TABLE_VALUE_MAX_ROWS} from './AddRowsButton';

type RecordAddRowsButtonProps = {
  checkedOptionCodes: SelectOptionCode[];
  toggleChange: (optionCode: SelectOptionCode) => void;
  maxRowCount?: number;
  /**
   * @internal
   */
  itemsPerPage?: number;
};

const RecordAddRowsButtonLoaderIcon = styled(LoaderIcon)`
  position: absolute;
  right: 15px;
  top: 18px;
  color: ${getColor('grey', 80)};
`;

const RecordAddRowsButton: React.FC<RecordAddRowsButtonProps> = ({
  checkedOptionCodes,
  toggleChange,
  maxRowCount = TABLE_VALUE_MAX_ROWS,
  itemsPerPage = RECORD_FETCHER_DEFAULT_LIMIT,
}) => {
  const translate = useTranslate();
  const security = useSecurity();
  const userContext = useUserContext();
  const {attribute} = useAttributeContext();
  const catalogLocale = userContext.get('catalogLocale');

  const [isOpen, open, close] = useBooleanState(false);
  const [searchValue, setSearchValue] = useState('');
  const debouncedSearchValue = useDebounce(searchValue, 200);
  const hasEditPermission = security.isGranted('pim_enrich_attribute_edit');
  const lowercaseCheckedOptionCodes = useMemo(() => checkedOptionCodes.map(code => code.toLowerCase()), [
    checkedOptionCodes,
  ]);
  const referenceEntityCode = attribute
    ? castReferenceEntityColumnDefinition(attribute?.table_configuration[0]).reference_entity_identifier
    : undefined;
  const {items, isLoading, handleNextPage} = useRecords({
    itemsPerPage,
    referenceEntityCode,
    isVisible: isOpen,
    searchValue: debouncedSearchValue,
  });

  const searchRef = React.createRef<HTMLInputElement>();

  const focus = (ref: React.RefObject<HTMLInputElement>) => {
    ref.current?.focus();
  };

  useEffect(() => {
    if (isOpen) {
      focus(searchRef);
    }
  }, [isOpen, searchRef]);

  const createToggleChange = useCallback((code: string) => () => toggleChange(code), [toggleChange]);

  return (
    <Dropdown>
      <Button onClick={open} level='secondary' size='small' ghost>
        {translate('pim_table_attribute.product_edit_form.add_rows')}
        <ArrowDownIcon />
      </Button>
      {isOpen && attribute && (
        <Dropdown.Overlay horizontalPosition='left' onClose={close}>
          <Dropdown.Header>
            {isLoading && <RecordAddRowsButtonLoaderIcon />}
            <Search
              inputRef={searchRef}
              onSearchChange={setSearchValue}
              placeholder={translate('pim_table_attribute.product_edit_form.search')}
              searchValue={searchValue}
              title={translate('pim_table_attribute.product_edit_form.search')}
            />
          </Dropdown.Header>
          <Dropdown.ItemCollection onNextPage={handleNextPage} data-testid={'item_collection'}>
            {items?.map((item, index) => {
              const label = getLabel(item.labels, catalogLocale, item.code);
              return (
                <Dropdown.Item
                  key={item.code}
                  disabled={!checkedOptionCodes.includes(item.code) && checkedOptionCodes.length >= maxRowCount}>
                  <Checkbox
                    checked={lowercaseCheckedOptionCodes.includes(item.code.toLowerCase())}
                    onChange={createToggleChange(item.code)}
                    data-testid={`checkbox-${index}`}>
                    <span title={item.code}>{label}</span>
                  </Checkbox>
                </Dropdown.Item>
              );
            })}
            {searchValue === '' && !isLoading && (items || []).length === 0 && (
              <Placeholder
                illustration={<AddingValueIllustration />}
                title={translate('pim_table_attribute.form.product.no_options')}>
                {!hasEditPermission &&
                  translate('pim_table_attribute.form.product.no_add_options_unallowed', {
                    attributeLabel: getLabel(attribute.labels, userContext.get('catalogLocale'), attribute.code),
                  })}
              </Placeholder>
            )}
            {searchValue !== '' && !isLoading && (items || []).length === 0 && (
              <Placeholder
                illustration={<AddingValueIllustration />}
                title={translate('pim_table_attribute.form.product.no_results')}
              />
            )}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export {RecordAddRowsButton};
