import React, {useState} from 'react';
import {AddingValueIllustration, Dropdown, Link, TableInput} from 'akeneo-design-system';
import {SelectOption, SelectOptionCode} from '../../models/TableConfiguration';
import {getLabel, useRouter, useSecurity, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {Attribute} from '../../models/Attribute';
import {CenteredHelper} from '../../shared/CenteredHelper';
import {LoadingPlaceholderContainer} from '../../shared/LoadingPlaceholder';

const BATCH_SIZE = 20;

type TableInputSelectProps = {
  value?: SelectOptionCode;
  onChange: (value: SelectOptionCode | undefined) => void;
  options?: SelectOption[];
  inError?: boolean;
  highlighted?: boolean;
  attribute: Attribute;
};

const FakeInput = styled.div`
  margin: 0 10px;
`;

const TableInputSelect: React.FC<TableInputSelectProps> = ({
  value,
  onChange,
  options,
  inError = false,
  highlighted = false,
  attribute,
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

export {TableInputSelect};
