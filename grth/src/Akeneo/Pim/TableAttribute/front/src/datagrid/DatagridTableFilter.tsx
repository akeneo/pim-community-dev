import React, {useEffect, useState} from 'react';
import {Button, Dropdown, useBooleanState} from 'akeneo-design-system';
import {
  AttributeCode,
  BackendTableFilterValue,
  isFilterValid,
  PendingBackendTableFilterValue,
  TableAttribute,
} from '../models';
import {AttributeFetcher} from '../fetchers';
import {getLabel, useRouter, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {FilterSelectorList} from './FilterSelectorList';
import {useIsMounted} from '../shared';
import {AttributeContext} from '../contexts';
import {DatagridTableCriteria} from './DatagridTableCriteria';
import {
  FilterBox,
  FilterButtonContainer,
  FilterContainer,
  FilterSectionTitle,
  FilterSectionTitleTitle,
} from '../shared/DatagridTableFilterStyle';

type DatagridTableFilterProps = {
  showLabel: boolean;
  canDisable: boolean;
  onDisable: () => void;
  attributeCode: AttributeCode;
  onChange: (value: BackendTableFilterValue) => void;
  initialDataFilter: PendingBackendTableFilterValue;
};

const DatagridTableFilter: React.FC<DatagridTableFilterProps> = ({
  showLabel,
  canDisable,
  onDisable,
  attributeCode,
  onChange,
  initialDataFilter,
  ...rest
}) => {
  const router = useRouter();
  const translate = useTranslate();
  const userContext = useUserContext();
  const catalogLocale = userContext.get('catalogLocale');
  const [isOpen, open, close] = useBooleanState();
  const [attribute, setAttribute] = useState<TableAttribute | undefined>();
  const [filterValue, setFilterValue] = useState<PendingBackendTableFilterValue>({
    value: initialDataFilter.value,
    operator: initialDataFilter.operator,
    column: initialDataFilter.column,
    row:
      typeof initialDataFilter.row === 'undefined' && typeof initialDataFilter.operator !== 'undefined'
        ? null
        : initialDataFilter.row,
  });
  const isMounted = useIsMounted();

  useEffect(() => {
    AttributeFetcher.fetch(router, attributeCode).then(attribute => {
      if (isMounted()) setAttribute(attribute as TableAttribute);
    });
  }, [attributeCode, isMounted, router]);

  const handleValidate = () => {
    if (filterValue && isFilterValid(filterValue)) {
      close();
      onChange({
        value: filterValue.value,
        column: filterValue.column,
        operator: filterValue.operator,
        row: filterValue.row || undefined,
      });
    }
  };

  const handleClose = () => {
    if (!isFilterValid(filterValue)) {
      close();
      onChange({});
      setFilterValue({});
    } else {
      handleValidate();
    }
  };

  return (
    <AttributeContext.Provider value={{attribute, setAttribute}}>
      <Dropdown {...rest}>
        {isOpen && attribute && (
          <Dropdown.Overlay onClose={handleClose}>
            <FilterContainer>
              <FilterSectionTitle title={getLabel(attribute.labels, catalogLocale, attribute.code)}>
                <FilterSectionTitleTitle>
                  {getLabel(attribute.labels, catalogLocale, attribute.code)}
                </FilterSectionTitleTitle>
              </FilterSectionTitle>
              <FilterSelectorList onChange={setFilterValue} initialFilter={filterValue} />
              <FilterButtonContainer>
                <Button onClick={handleValidate} disabled={!isFilterValid(filterValue)}>
                  {translate('pim_common.update')}
                </Button>
              </FilterButtonContainer>
            </FilterContainer>
          </Dropdown.Overlay>
        )}
        <FilterBox className='AknFilterBox-filter' onClick={open}>
          {showLabel && attribute && (
            <span className='AknFilterBox-filterLabel'>
              {getLabel(attribute.labels, catalogLocale, attribute.code)}
            </span>
          )}
          <DatagridTableCriteria filterValue={filterValue} />
          <span className='AknFilterBox-filterCaret' />
        </FilterBox>
        {canDisable && (
          <div className='AknFilterBox-disableFilter AknIconButton AknIconButton--remove' onClick={onDisable} />
        )}
      </Dropdown>
    </AttributeContext.Provider>
  );
};

export {DatagridTableFilter};
