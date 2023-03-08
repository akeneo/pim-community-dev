import React, {FC, useCallback, useEffect, useRef, useState, MouseEvent} from 'react';
import styled from 'styled-components';
import {Search, useAutoFocus, Table, Badge, CheckboxChecked} from 'akeneo-design-system';
import {
  useTranslate,
  useFeatureFlags,
  useUserContext,
  getLabel,
  useRouter,
  useSecurity,
} from '@akeneo-pim-community/shared';
import {AttributeGroup} from '../../../models';
import {NoResults} from '../../shared';
import {useAttributeGroupPermissions, useAttributeGroupsIndexState, useFilteredAttributeGroups} from '../../../hooks';

const Wrapper = styled.div`
  display: flex;
  flex-direction: column;
`;

const TableWrapper = styled.div<{isSelectable: boolean}>`
  ${({isSelectable}) => (isSelectable ? 'margin-left: -40px;' : '')}
`;

type Props = {
  attributeGroups: AttributeGroup[];
  onGroupCountChange: (newGroupCount: number) => void;
  isItemSelected: (attributeGroup: AttributeGroup) => boolean;
  selectionState: CheckboxChecked;
  onSelectionChange: (attributeGroup: AttributeGroup, selected: boolean) => void;
  selectedCount: number;
  onSelectAllChange: (mode: boolean) => void;
};

const AttributeGroupsDataGrid: FC<Props> = ({
  attributeGroups,
  onGroupCountChange,
  isItemSelected,
  selectionState,
  onSelectionChange,
}) => {
  const {refreshOrder} = useAttributeGroupsIndexState();
  const {sortGranted, editGranted} = useAttributeGroupPermissions();
  const catalogLocale = useUserContext().get('catalogLocale');
  const {filteredGroups, search} = useFilteredAttributeGroups(attributeGroups);
  const translate = useTranslate();
  const router = useRouter();
  const [searchString, setSearchString] = useState('');
  const inputRef = useRef<HTMLInputElement>(null);
  const {isEnabled} = useFeatureFlags();
  const {isGranted} = useSecurity();

  useAutoFocus(inputRef);

  const onSearch = (searchValue: string) => {
    setSearchString(searchValue);
    search(searchValue);
  };

  const handleRowClick = useCallback(
    (attributeGroup: AttributeGroup) => (event: MouseEvent<HTMLTableRowElement>) => {
      const url = router.generate('pim_enrich_attributegroup_edit', {
        identifier: attributeGroup.code,
      });

      if (event.metaKey || event.ctrlKey) {
        const newTab = window.open(`#${url}`, '_blank');
        newTab?.focus();

        return;
      }

      router.redirect(url);
    },
    [router]
  );

  useEffect(() => {
    onGroupCountChange(filteredGroups.length);
  }, [filteredGroups.length, onGroupCountChange]);

  const shouldDisplayPlaceholder = '' !== searchString && 0 === filteredGroups.length;
  const shouldDisplayDQICell = isEnabled('data_quality_insights');
  const canDragAndDrop = sortGranted && false === selectionState && '' === searchString;
  const canSelect = isEnabled('attribute_groups_mass_delete') && isGranted('pim_enrich_attributegroup_mass_delete');

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
      {shouldDisplayPlaceholder ? (
        <NoResults
          title={translate('pim_common.no_search_result')}
          subtitle={translate('pim_datagrid.no_results_subtitle')}
        />
      ) : (
        <TableWrapper isSelectable={canSelect}>
          <Table
            isDragAndDroppable={canDragAndDrop}
            isSelectable={canSelect}
            onReorder={order => refreshOrder(order.map(index => attributeGroups[index]))}
          >
            <Table.Header>
              <Table.HeaderCell>{translate('pim_common.label')}</Table.HeaderCell>
              <Table.HeaderCell>
                {translate('pim_enrich.entity.attribute_group.grid.columns.attribute_count')}
              </Table.HeaderCell>
              {shouldDisplayDQICell && (
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
                  onSelectToggle={selected => onSelectionChange(attributeGroup, selected)}
                  onClick={editGranted ? event => handleRowClick(attributeGroup)(event) : undefined}
                >
                  <Table.Cell rowTitle={true}>
                    {getLabel(attributeGroup.labels, catalogLocale, attributeGroup.code)}
                  </Table.Cell>
                  <Table.Cell>{attributeGroup.attribute_count}</Table.Cell>
                  {shouldDisplayDQICell && (
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
      )}
    </Wrapper>
  );
};

export {AttributeGroupsDataGrid};
