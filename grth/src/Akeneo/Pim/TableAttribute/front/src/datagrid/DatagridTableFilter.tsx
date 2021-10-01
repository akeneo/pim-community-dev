import React, {useEffect, useState} from 'react';
import {Button, Dropdown, getColor, SectionTitle, useBooleanState} from 'akeneo-design-system';
import {TableAttribute} from '../models';
import {AttributeFetcher} from '../fetchers';
import {getLabel, useRouter, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {FilterValuesMapping} from './FilterValues';
import styled from 'styled-components';
import {BackendTableFilterValue, FilterSelectorList, PendingTableFilterValue} from "./FilterSelectorList";

const FilterSectionTitleTitle = styled(SectionTitle.Title)`
  color: ${getColor('brand', 100)};
`;
const FilterSectionTitle = styled(SectionTitle)`
  border-bottom-color: ${getColor('brand', 100)};
`;

const FilterContainer = styled.div`
  width: 280px;
  padding: 0 20px 10px;
`;

const FilterButtonContainer = styled.div`
  text-align: center;
`;

type DatagridTableFilterProps = {
  showLabel: boolean;
  label: string;
  canDisable: boolean;
  onDisable: () => void;
  attributeCode: string;
  onChange: (value: BackendTableFilterValue) => void;
  filterValuesMapping: FilterValuesMapping;
};

const DatagridTableFilter: React.FC<DatagridTableFilterProps> = ({
  showLabel,
  label,
  canDisable,
  onDisable,
  attributeCode,
  onChange,
  filterValuesMapping,
}) => {
  const router = useRouter();
  const translate = useTranslate();
  const userContext = useUserContext();
  const catalogLocale = userContext.get('catalogLocale');
  const [isOpen, open, close] = useBooleanState();
  const [attribute, setAttribute] = useState<TableAttribute | undefined>();
  const [filterValue, setFilterValue] = useState<PendingTableFilterValue>({
    operator: '',
  });

  useEffect(() => {
    AttributeFetcher.fetch(router, attributeCode).then(attribute => {
      setAttribute(attribute as TableAttribute);
    });
  }, []);

  const handleValidate = () => {
    close();
    onChange({
      row: filterValue.row?.code,
      column: filterValue.column?.code as string,
      operator: filterValue.operator as string,
      value: filterValue.value,
    });
  };

  // TODO Think about wording and translate this CPM-378
  let criteriaLabel = 'All';
  if (typeof filterValue.column !== 'undefined') {
    criteriaLabel = '';
    criteriaLabel +=
      typeof filterValue.row === 'undefined' ? 'Any' : getLabel(filterValue.row.labels, catalogLocale, filterValue.row.code) + ' ';
    criteriaLabel += getLabel(filterValue.column.labels, catalogLocale, filterValue.column.code) + ' ';
    criteriaLabel +=
      typeof filterValue.operator !== 'undefined' ? translate(`pim_common.operators.${filterValue.operator}`) + ' ' : '';
    criteriaLabel += typeof filterValue.value !== 'undefined' ? JSON.stringify(filterValue.value) : '';
  }

  return (
    <Dropdown>
      {isOpen && attribute && (
        <Dropdown.Overlay onClose={close}>
          <FilterContainer>
            <FilterSectionTitle title={label}>
              <FilterSectionTitleTitle>{label}</FilterSectionTitleTitle>
            </FilterSectionTitle>
            <FilterSelectorList
              attribute={attribute}
              filterValuesMapping={filterValuesMapping}
              onChange={setFilterValue}
              initialFilter={{}}
            />
            <FilterButtonContainer>
              <Button onClick={handleValidate}>{translate('pim_common.update')}</Button>
            </FilterButtonContainer>
          </FilterContainer>
        </Dropdown.Overlay>
      )}
      <div className='AknFilterBox-filter' onClick={open}>
        {showLabel && <span className='AknFilterBox-filterLabel'>{label}</span>}
        <span className='AknFilterBox-filterCriteria AknFilterBox-filterCriteria--limited' title={criteriaLabel}>
          {criteriaLabel}
        </span>
        <span className='AknFilterBox-filterCaret' />
      </div>
      {canDisable && (
        <div className='AknFilterBox-disableFilter AknIconButton AknIconButton--remove' onClick={onDisable} />
      )}
    </Dropdown>
  );
};

export {DatagridTableFilter};
