import React, {useState} from 'react';
import {AddingValueIllustration, Dropdown, Link, TableInput} from 'akeneo-design-system';
import {Attribute, ColumnCode, SelectOption, SelectOptionCode} from '../../models';
import {getLabel, useRouter, useSecurity, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {CenteredHelper, LoadingPlaceholderContainer} from '../../shared';
import {useFetchOptions} from '../useFetchOptions';
import {CellInput} from './index';

const BATCH_SIZE = 20;

type TableInputSelectProps = {
  value?: SelectOptionCode;
  onChange: (value: SelectOptionCode | undefined) => void;
  inError?: boolean;
  highlighted?: boolean;
  attribute: Attribute;
  columnCode: ColumnCode;
};

const FakeInput = styled.div`
  margin: 0 10px;
  height: 20px;
`;

const SelectInput: React.FC<TableInputSelectProps> = ({
  value,
  onChange,
  inError = false,
  highlighted = false,
  attribute,
  columnCode,
  ...rest
}) => {
  const translate = useTranslate();
  const userContext = useUserContext();
  const security = useSecurity();
  const router = useRouter();

  const [searchValue, setSearchValue] = React.useState<string>('');
  const [numberOfDisplayedItems, setNumberOfDisplayedItems] = useState<number>(BATCH_SIZE);
  const [closeTick, setCloseTick] = React.useState<boolean>(false);

  const hasEditPermission = security.isGranted('pim_enrich_attribute_edit');

  const {getOptionsFromColumnCode} = useFetchOptions(attribute.table_configuration, attribute.code, []);
  const options = getOptionsFromColumnCode(columnCode);

  const isLoading = typeof options === 'undefined';
  let option = null;
  if (value && typeof options !== 'undefined') {
    option = options.find(option => option.code.toLowerCase() === value.toLowerCase());
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

  const handleRedirect = () => {
    setCloseTick(!closeTick);
    router.redirect(router.generate('pim_enrich_attribute_edit', {code: attribute.code}));
  };

  if (isLoading) {
    return (
      <LoadingPlaceholderContainer>
        <FakeInput>{translate('pim_common.loading')}</FakeInput>
      </LoadingPlaceholderContainer>
    );
  }

  return (
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
      {...rest}>
      {itemsToDisplay.map(option => {
        return (
          <Dropdown.Item key={option.code} onClick={() => onChange(option.code)}>
            {getLabel(option.labels, userContext.get('catalogLocale'), option.code)}
          </Dropdown.Item>
        );
      })}
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
            <CenteredHelper.Title>{translate('pim_table_attribute.form.attribute.no_options')}</CenteredHelper.Title>
            {translate('pim_table_attribute.form.attribute.please_try_again')}
          </CenteredHelper>
        </CenteredHelper.Container>
      )}
    </TableInput.Select>
  );
};

const renderer: CellInput = ({row, columnDefinition, onChange, inError, attribute, highlighted, ...rest}) => {
  const cell = row[columnDefinition.code] as SelectOptionCode | undefined;

  return (
    <SelectInput
      attribute={attribute}
      highlighted={highlighted}
      value={cell}
      onChange={onChange}
      inError={inError}
      columnCode={columnDefinition.code}
      {...rest}
    />
  );
};

export default renderer;
