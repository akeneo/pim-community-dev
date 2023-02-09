import React, {FC, useEffect, useRef, useState} from 'react';
import {AttributeGroup} from '../../../models';
import {NoResults} from '../../shared';
import {
  Search,
  useAutoFocus,
  Table,
  Badge,
  Checkbox,
  Dropdown,
  ArrowDownIcon,
  useBooleanState,
} from 'akeneo-design-system';
import {getLabel} from 'pimui/js/i18n';
import {useAttributeGroupPermissions, useAttributeGroupsIndexState, useFilteredAttributeGroups} from '../../../hooks';
import {useDebounceCallback, useTranslate, useFeatureFlags, useUserContext} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
const FeatureFlags = require('pim/feature-flags');

type Props = {
  attributeGroups: AttributeGroup[];
  onGroupCountChange: (newGroupCount: number) => void;
  isItemSelected: (attributeGroup: AttributeGroup) => boolean;
  selectionState: boolean | 'mixed';
  onSelectionChange: (attributeGroup: AttributeGroup, selected: boolean) => void;
  selectedCount: number;
  onSelectAllChange: (mode: boolean) => void;
};

const Wrapper = styled.div`
  display: flex;
  flex-direction: column;
`;

const TableWrapper = styled.div`
  margin-left: -40px;
`;

const InfoTop = styled.div`
  display: flex;
  gap: 15px;
  padding: 38px 20px;
`;

const AttributeGroupsDataGrid: FC<Props> = ({
  attributeGroups,
  onGroupCountChange,
  isItemSelected,
  selectionState,
  onSelectionChange,
  selectedCount,
  onSelectAllChange,
}) => {
  const {refreshOrder} = useAttributeGroupsIndexState();
  const {sortGranted} = useAttributeGroupPermissions();
  const userContext = useUserContext();
  const {filteredGroups, search} = useFilteredAttributeGroups(attributeGroups);
  const translate = useTranslate();
  const [searchString, setSearchString] = useState('');
  const inputRef = useRef<HTMLInputElement>(null);
  const featureFlags = useFeatureFlags();
  const [isDropdownOpen, openDropdown, closeDropdown] = useBooleanState();

  useAutoFocus(inputRef);

  const debouncedSearch = useDebounceCallback(search, 300);

  const onSearch = (searchValue: string) => {
    setSearchString(searchValue);
    debouncedSearch(searchValue);
  };

  const onReorder = async (order: number[]) => {
    await refreshOrder(order.map(index => groups[index]));
  };

  useEffect(() => {
    onGroupCountChange(filteredGroups.length);
  }, [filteredGroups.length]);

  return (
    <Wrapper>
      <Search
        sticky={0}
        placeholder={translate('pim_common.search')}
        searchValue={searchString}
        onSearchChange={onSearch}
        inputRef={inputRef}
      >
        <Search.ResultCount>
          {translate('pim_common.result_count', {itemsCount: filteredGroups.length}, filteredGroups.length)}
        </Search.ResultCount>
      </Search>
      {searchString !== '' && filteredGroups.length === 0 ? (
        <NoResults
          title={translate('pim_enrich.entity.attribute_group.grid.no_search_result')}
          subtitle={translate('pim_datagrid.no_results_subtitle')}
        />
      ) : (
        <>
          <InfoTop>
            <Checkbox checked={selectionState} onChange={() => {}} />
            <Dropdown>
              <ArrowDownIcon onClick={openDropdown} />
              {isDropdownOpen && (
                <Dropdown.Overlay onClose={closeDropdown}>
                  <Dropdown.Header>
                    <Dropdown.Title>Select</Dropdown.Title>
                  </Dropdown.Header>
                  <Dropdown.ItemCollection>
                    <Dropdown.Item onClick={() => onSelectAllChange(true)}>All Attribute groups</Dropdown.Item>
                    <Dropdown.Item onClick={() => onSelectAllChange(false)}>No Attribute groups</Dropdown.Item>
                  </Dropdown.ItemCollection>
                </Dropdown.Overlay>
              )}
            </Dropdown>
            <p>{translate('pim_enrich.entity.attribute_group.selected', {count: selectedCount}, selectedCount)}</p>
          </InfoTop>
          <TableWrapper>
            <Table
              isDragAndDroppable={sortGranted && 'mixed' !== selectionState && !selectionState}
              isSelectable={true}
              onReorder={order => refreshOrder(order.map(index => attributeGroups[index]))}
            >
              <Table.Header>
                <Table.HeaderCell>{translate('pim_enrich.entity.attribute_group.grid.columns.name')}</Table.HeaderCell>
                {featureFlags.isEnabled('data_quality_insights') && (
                  <Table.HeaderCell>
                    {translate('akeneo_data_quality_insights.attribute_group.dqi_status')}
                  </Table.HeaderCell>
                )}
              </Table.Header>
              <Table.Body>
                {filteredGroups.map(attributeGroup => (
                  <Table.Row
                    key={attributeGroup.code}
                    isSelected={isItemSelected(attributeGroup)}
                    onSelectToggle={(selected: boolean) => onSelectionChange(attributeGroup, selected)}
                  >
                    <Table.Cell>
                      {getLabel(attributeGroup.labels, userContext.get('catalogLocale'), attributeGroup.code)}
                    </Table.Cell>
                    {featureFlags.isEnabled('data_quality_insights') && (
                      <Table.Cell>
                        <Badge level={attributeGroup.is_dqi_activated ? 'primary' : 'danger'}>
                          {translate(
                            `akeneo_data_quality_insights.attribute_group.${
                              attributeGroup.is_dqi_activated ? 'activated' : 'disabled'
                            }`
                          )}
                        </Badge>
                      </Table.Cell>
                    )}
                  </Table.Row>
                ))}
              </Table.Body>
            </Table>
          </TableWrapper>
        </>
      )}
    </Wrapper>
  );
};

export {AttributeGroupsDataGrid};
