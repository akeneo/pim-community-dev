import React, {useEffect, useState} from 'react';
import {Button, Dropdown, SelectInput, useBooleanState} from 'akeneo-design-system';
import {AttributeCode, NotEmptyTableFilterValue, TableAttribute} from '../models';
import {AttributeFetcher} from '../fetchers';
import {getLabel, useRouter, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {useIsMounted} from '../shared';
import {AttributeContext} from '../contexts';
import {
  FilterBox,
  FilterButtonContainer,
  FilterContainer,
  FilterSectionTitle,
  FilterSectionTitleTitle,
} from '../shared/DatagridTableFilterStyle';
import styled from 'styled-components';

type NotEmptyDatagridTableFilterProps = {
  showLabel: boolean;
  canDisable: boolean;
  onDisable: () => void;
  attributeCode: AttributeCode;
  onChange: (value: NotEmptyTableFilterValue) => void;
  initialDataFilter: NotEmptyTableFilterValue;
};

const StyledSelectInput = styled(SelectInput)`
  margin: 20px 0 10px 0;
`;

const NotEmptyDatagridTableFilter: React.FC<NotEmptyDatagridTableFilterProps> = ({
  showLabel,
  canDisable,
  onDisable,
  attributeCode,
  onChange,
  initialDataFilter = {},
  ...rest
}) => {
  const router = useRouter();
  const translate = useTranslate();
  const catalogLocale = useUserContext().get('catalogLocale');
  const [isOpen, open, close] = useBooleanState();
  const [attribute, setAttribute] = useState<TableAttribute | undefined>();
  const [filterValue, setFilterValue] = useState<NotEmptyTableFilterValue>(initialDataFilter);
  const isMounted = useIsMounted();

  useEffect(() => {
    AttributeFetcher.fetch(router, attributeCode).then(attribute => {
      if (isMounted()) {
        const tableAttribute = attribute as TableAttribute;
        setAttribute(tableAttribute);
      }
    });
  }, []);

  const handleValidate = () => {
    if (filterValue) {
      close();
      onChange(filterValue);
    }
  };

  const handleClose = () => {
    if (!filterValue?.operator) {
      close();
      onChange({});
      setFilterValue({});
    } else {
      handleValidate();
    }
  };

  const handleChange = (value: string | null) => {
    setFilterValue({operator: (value as 'NOT EMPTY' | null) || undefined});
  };

  const criteriaHint = translate(
    filterValue?.operator === 'NOT EMPTY' ? `pim_common.operators.NOT EMPTY` : 'pim_common.all'
  );

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
              <StyledSelectInput
                clearLabel={translate('pim_common.clear_value')}
                clearable
                emptyResultLabel={translate('pim_common.no_result')}
                onChange={handleChange}
                placeholder={translate('pim_table_attribute.datagrid.select_your_operator')}
                value={(filterValue.operator as string) || null}
                openLabel={translate('pim_common.open')}
              >
                <SelectInput.Option title={translate(`pim_common.operators.NOT EMPTY`)} value='NOT EMPTY'>
                  {translate(`pim_common.operators.NOT EMPTY`)}
                </SelectInput.Option>
              </StyledSelectInput>
              <FilterButtonContainer>
                <Button onClick={handleValidate}>{translate('pim_common.update')}</Button>
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
          <span className='AknFilterBox-filterCriteria AknFilterBox-filterCriteria--limited' title={criteriaHint}>
            {criteriaHint}
          </span>
          <span className='AknFilterBox-filterCaret' />
        </FilterBox>
        {canDisable && (
          <div className='AknFilterBox-disableFilter AknIconButton AknIconButton--remove' onClick={onDisable} />
        )}
      </Dropdown>
    </AttributeContext.Provider>
  );
};

export {NotEmptyDatagridTableFilter};
