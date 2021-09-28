import React, {useState} from 'react';
import {
  AddingValueIllustration,
  ArrowDownIcon,
  Badge,
  Button,
  Checkbox,
  Dropdown,
  Link,
  Search,
  useBooleanState,
} from 'akeneo-design-system';
import {getLabel, useRouter, useSecurity, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {ColumnCode, SelectOption, SelectOptionCode, TableAttribute} from '../models';
import styled from 'styled-components';
import {CenteredHelper} from '../shared';
import {SelectOptionRepository} from '../repositories';

const BATCH_SIZE = 20;

type AddRowsButtonProps = {
  attribute: TableAttribute;
  columnCode: ColumnCode;
  checkedOptionCodes: SelectOptionCode[];
  toggleChange: (optionCode: SelectOptionCode) => void;
  maxRowCount?: number;
};

const NoEditPermission = styled(Badge)`
  position: absolute;
  z-index: 2;
  right: 20px;
  top: 24px;
`;

type Option = {
  code: string;
  label: string;
};

const AddRowsButton: React.FC<AddRowsButtonProps> = ({attribute, columnCode, checkedOptionCodes, toggleChange, maxRowCount = 100}) => {
  const router = useRouter();
  const translate = useTranslate();
  const security = useSecurity();
  const userContext = useUserContext();

  const [isOpen, open, close] = useBooleanState(false);
  const [searchValue, setSearchValue] = useState('');
  const [items, setItems] = useState<Option[] | undefined>(undefined);
  const [numberOfDisplayedItems, setNumberOfDisplayedItems] = useState<number>(BATCH_SIZE);

  const hasEditPermission = security.isGranted('pim_enrich_attribute_edit');
  const lowercaseCheckedOptionCodes = checkedOptionCodes.map(code => code.toLowerCase());

  const searchRef = React.createRef<HTMLInputElement>();

  const focus = (ref: React.RefObject<HTMLInputElement>) => {
    ref.current?.focus();
  };

  React.useEffect(() => {
    if (isOpen) {
      focus(searchRef);
    }
  }, [isOpen]);

  React.useEffect(() => {
    if (isOpen && typeof items === 'undefined') {
      SelectOptionRepository.findFromColumn(router, attribute.code, columnCode).then(selectOptions => {
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
      return item.label.toLowerCase().includes(searchValue.toLowerCase());
    })
    .slice(0, numberOfDisplayedItems);

  const handleRedirect = () => {
    close();
    router.redirect(router.generate('pim_enrich_attribute_edit', {code: attribute.code}));
  };

  return (
    <Dropdown>
      <Button onClick={open} level='secondary' size='small' ghost>
        {translate('pim_table_attribute.product_edit_form.add_rows')}
        <ArrowDownIcon />
      </Button>
      {isOpen && (
        <Dropdown.Overlay verticalPosition='down' onClose={close}>
          <Dropdown.Header>
            {searchValue === '' && itemsToDisplay.length === 0 && !hasEditPermission && (
              <NoEditPermission level='danger'>
                {translate('pim_table_attribute.form.product.no_edit_permission')}
              </NoEditPermission>
            )}
            <Search
              inputRef={searchRef}
              onSearchChange={handleSearchValue}
              placeholder={translate('pim_table_attribute.product_edit_form.search')}
              searchValue={searchValue}
              title={translate('pim_table_attribute.product_edit_form.search')}
            />
          </Dropdown.Header>
          <Dropdown.ItemCollection onNextPage={handleNextPage} data-testid={'item_collection'}>
            {itemsToDisplay.map((item, index) => (
              <Dropdown.Item key={item.code} disabled={!checkedOptionCodes.includes(item.code) && checkedOptionCodes.length >= maxRowCount}>
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
                    {translate('pim_table_attribute.form.product.no_add_options_title')}
                  </CenteredHelper.Title>
                  {hasEditPermission ? (
                    <div>
                      {translate('pim_table_attribute.form.product.no_add_options', {
                        attributeLabel: getLabel(attribute.labels, userContext.get('catalogLocale'), attribute.code),
                      })}{' '}
                      <Link onClick={handleRedirect}>
                        {translate('pim_table_attribute.form.product.no_add_options_link')}
                      </Link>
                    </div>
                  ) : (
                    translate('pim_table_attribute.form.product.no_add_options_unallowed', {
                      attributeLabel: getLabel(attribute.labels, userContext.get('catalogLocale'), attribute.code),
                    })
                  )}
                </CenteredHelper>
              </CenteredHelper.Container>
            )}
            {searchValue !== '' && itemsToDisplay.length === 0 && (
              <CenteredHelper.Container>
                <CenteredHelper illustration={<AddingValueIllustration />}>
                  <CenteredHelper.Title>
                    {translate('pim_table_attribute.form.attribute.no_options')}
                  </CenteredHelper.Title>
                  {translate('pim_table_attribute.form.attribute.please_try_again')}
                </CenteredHelper>
              </CenteredHelper.Container>
            )}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export {AddRowsButton};
