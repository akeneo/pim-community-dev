import React, {useState} from 'react';
import {
  useBooleanState,
  Dropdown,
  ArrowDownIcon,
  Search,
  Button,
  Checkbox,
  AddingValueIllustration,
  getFontSize,
  getColor,
  Link,
  Badge,
} from 'akeneo-design-system';
import {useRouter, getLabel, useUserContext, useTranslate, useSecurity} from '@akeneo-pim-community/shared';
import {ColumnCode, SelectOption, SelectOptionCode} from '../models/TableConfiguration';
import {getSelectOptions} from '../repositories/SelectOption';
import styled from 'styled-components';
import {TableAttribute} from '../models/Attribute';

const BATCH_SIZE = 20;

type AddRowsButtonProps = {
  attribute: TableAttribute;
  columnCode: ColumnCode;
  checkedOptionCodes: SelectOptionCode[];
  toggleChange: (optionCode: SelectOptionCode) => void;
};

const CenteredHelper = styled.div`
  text-align: center;
  max-width: 258px;
  padding: 0 20px;
  & > * {
    display: block;
    margin: auto;
  }
`;

const CenteredHelperTitle = styled.div`
  font-size: ${getFontSize('big')};
  color: ${getColor('grey', 140)};
`;

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

const AddRowsButton: React.FC<AddRowsButtonProps> = ({attribute, columnCode, checkedOptionCodes, toggleChange}) => {
  const router = useRouter();
  const translate = useTranslate();
  const security = useSecurity();
  const userContext = useUserContext();

  const [isOpen, open, close] = useBooleanState(false);
  const [searchValue, setSearchValue] = useState('');
  const [items, setItems] = useState<Option[] | undefined>(undefined);
  const [numberOfDisplayedItems, setNumberOfDisplayedItems] = useState<number>(BATCH_SIZE);

  const hasEditPermission = security.isGranted('pim_enrich_attribute_edit');

  React.useEffect(() => {
    if (isOpen && typeof items === 'undefined') {
      getSelectOptions(router, attribute.code, columnCode).then(selectOptions => {
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
              onSearchChange={handleSearchValue}
              placeholder={translate('pim_table_attribute.product_edit_form.search')}
              searchValue={searchValue}
              title={translate('pim_table_attribute.product_edit_form.search')}
            />
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
            {searchValue === '' && itemsToDisplay.length === 0 && (
              <CenteredHelper>
                <AddingValueIllustration size={100} />
                <CenteredHelperTitle>
                  {translate('pim_table_attribute.form.product.no_add_options_title')}
                </CenteredHelperTitle>
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
            )}
            {searchValue !== '' && itemsToDisplay.length === 0 && (
              <CenteredHelper>
                <AddingValueIllustration size={120} />
                <CenteredHelperTitle>
                  {translate('pim_table_attribute.form.attribute.no_options')}
                </CenteredHelperTitle>
                {translate('pim_table_attribute.form.attribute.please_try_again')}
              </CenteredHelper>
            )}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export {AddRowsButton};
