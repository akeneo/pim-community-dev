import React, {FC, useEffect, useRef, useState} from 'react';
import {AttributeGroup} from '../../../models';
import {NoResults} from '../../shared';
import {Search, useAutoFocus, Table, Badge} from 'akeneo-design-system';
import {getLabel} from 'pimui/js/i18n';
import {useAttributeGroupPermissions, useAttributeGroupsIndexState, useFilteredAttributeGroups} from '../../../hooks';
import {useDebounceCallback, useTranslate, useFeatureFlags, useUserContext} from '@akeneo-pim-community/shared';
import styled from 'styled-components';

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

const AttributeGroupsDataGrid: FC<Props> = ({
  attributeGroups,
  onGroupCountChange,
  isItemSelected,
  selectionState,
  onSelectionChange,
}) => {
  const {refreshOrder} = useAttributeGroupsIndexState();
  const {sortGranted} = useAttributeGroupPermissions();
  const userContext = useUserContext();
  const {filteredGroups, search} = useFilteredAttributeGroups(attributeGroups);
  const translate = useTranslate();
  const [searchString, setSearchString] = useState('');
  const inputRef = useRef<HTMLInputElement>(null);
  const featureFlags = useFeatureFlags();

  useAutoFocus(inputRef);

  const debouncedSearch = useDebounceCallback(search, 300);

  const onSearch = (searchValue: string) => {
    setSearchString(searchValue);
    debouncedSearch(searchValue);
  };

  useEffect(() => {
    onGroupCountChange(filteredGroups.length);
  }, [filteredGroups.length, onGroupCountChange]);

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
          <TableWrapper>
            <Table
              isDragAndDroppable={sortGranted && 'mixed' !== selectionState && !selectionState}
              isSelectable={true}
              onReorder={order => refreshOrder(order.map(index => attributeGroups[index]))}
            >
              <Table.Header>
                <Table.HeaderCell>{translate('pim_enrich.entity.attribute_group.grid.columns.name')}</Table.HeaderCell>
                <Table.HeaderCell>
                  {translate('pim_enrich.entity.attribute_group.grid.columns.attribute_count')}
                </Table.HeaderCell>
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
                    <Table.Cell>{attributeGroup.attribute_count}</Table.Cell>
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
