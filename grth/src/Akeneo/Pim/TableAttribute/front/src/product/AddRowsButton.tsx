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
import {
  getLabel,
  NotificationLevel,
  useNotify,
  useRouter,
  useSecurity,
  useTranslate,
  useUserContext,
} from '@akeneo-pim-community/shared';
import {ColumnCode, SelectColumnDefinition, SelectOption, SelectOptionCode} from '../models';
import styled from 'styled-components';
import {CenteredHelper} from '../shared';
import {SelectOptionRepository} from '../repositories';
import {ManageOptionsModal} from '../attribute';
import {useAttributeContext} from '../contexts';
import {useFetchOptions} from './useFetchOptions';

const BATCH_SIZE = 20;

type AddRowsButtonProps = {
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
  const router = useRouter();
  const translate = useTranslate();
  const security = useSecurity();
  const userContext = useUserContext();
  const notify = useNotify();
  const {attribute, setAttribute} = useAttributeContext();

  const [isOpen, open, close] = useBooleanState(false);
  const [isManageOptionsOpen, openManageOptions, closeManageOptions] = useBooleanState(false);
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

  const handleRedirect = () => {
    close();
    if (attribute) {
      router.redirect(router.generate('pim_enrich_attribute_edit', {code: attribute.code}));
    }
  };

  const handleSaveOptions = (selectOptions: SelectOption[]) => {
    if (attribute) {
      SelectOptionRepository.save(router, attribute, columnCode, selectOptions).then(result => {
        result
          ? setAttribute({...attribute})
          : notify(NotificationLevel.ERROR, translate('pim_table_attribute.form.product.save_options_error'));
      });
    }
  };

  return (
    <Dropdown>
      <Button onClick={open} level='secondary' size='small' ghost>
        {translate('pim_table_attribute.product_edit_form.add_rows')}
        <ArrowDownIcon />
      </Button>
      {isOpen && attribute && (
        <Dropdown.Overlay horizontalPosition='left' onClose={close}>
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
          {hasEditPermission && (
            <EditOptionsContainer>
              <Button onClick={openManageOptions} ghost level='secondary'>
                {translate('pim_table_attribute.form.product.edit_options')}
              </Button>
              {isManageOptionsOpen && (
                <ManageOptionsModal
                  onClose={closeManageOptions}
                  attribute={attribute}
                  columnDefinition={attribute.table_configuration[0] as SelectColumnDefinition}
                  onChange={handleSaveOptions}
                  confirmLabel={translate('pim_common.save')}
                />
              )}
            </EditOptionsContainer>
          )}
        </Dropdown.Overlay>
      )}
    </Dropdown>
  );
};

export {AddRowsButton};
