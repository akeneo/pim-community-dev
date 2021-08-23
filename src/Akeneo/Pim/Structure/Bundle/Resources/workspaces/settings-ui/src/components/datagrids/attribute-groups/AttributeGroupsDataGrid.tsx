import React, {FC, useEffect, useRef, useState} from 'react';
import {Search, useAutoFocus} from 'akeneo-design-system';
import {useDebounceCallback, useTranslate} from '@akeneo-pim-community/shared';
import {
  useAttributeGroupPermissions,
  useAttributeGroupsIndexState,
  useFilteredAttributeGroups,
  useGetAttributeGroupLabel,
} from '../../../hooks';
import {AttributeGroup} from '../../../models';
import {DataGrid, NoResults} from '../../shared';
import {StatusBadge} from './StatusBadge';

const FeatureFlags = require('pim/feature-flags');

type Props = {
  groups: AttributeGroup[];
  onGroupCountChange: (newGroupCount: number) => void;
};

const AttributeGroupsDataGrid: FC<Props> = ({groups, onGroupCountChange}) => {
  const {refreshOrder, compare, saveOrder, redirect} = useAttributeGroupsIndexState();
  const {sortGranted, editGranted} = useAttributeGroupPermissions();
  const getLabel = useGetAttributeGroupLabel();
  const {filteredGroups, search} = useFilteredAttributeGroups(groups);
  const translate = useTranslate();
  const [searchString, setSearchString] = useState('');
  const inputRef = useRef<HTMLInputElement>(null);

  useAutoFocus(inputRef);

  const debouncedSearch = useDebounceCallback(search, 300);

  const onSearch = (searchValue: string) => {
    setSearchString(searchValue);
    debouncedSearch(searchValue);
  };

  useEffect(() => {
    onGroupCountChange(filteredGroups.length);
  }, [filteredGroups.length]);

  return (
    <>
      <Search
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
        <DataGrid
          isReorderAllowed={sortGranted}
          isReorderActive={filteredGroups.length === groups.length}
          dataSource={filteredGroups}
          handleAfterMove={refreshOrder}
          compareData={compare}
          isFilterable={true}
        >
          <DataGrid.HeaderRow>
            <DataGrid.Cell>{translate('pim_enrich.entity.attribute_group.grid.columns.name')}</DataGrid.Cell>
            {FeatureFlags.isEnabled('data_quality_insights') && (
              <DataGrid.Cell>{translate('akeneo_data_quality_insights.attribute_group.dqi_status')}</DataGrid.Cell>
            )}
          </DataGrid.HeaderRow>
          <DataGrid.Body
            onRowClick={(group: AttributeGroup) => {
              if (editGranted) {
                redirect(group);
              }
            }}
            onRowMoveEnd={() => {
              (async () => saveOrder())();
            }}
          >
            {filteredGroups.map(group => (
              <DataGrid.Row key={group.code} data={group}>
                <DataGrid.Cell rowTitle style={{width: 71}}>
                  {getLabel(group)}
                </DataGrid.Cell>
                {FeatureFlags.isEnabled('data_quality_insights') && (
                  <DataGrid.Cell>
                    <StatusBadge isActivated={group.isDqiActivated ? true : false} />
                  </DataGrid.Cell>
                )}
              </DataGrid.Row>
            ))}
          </DataGrid.Body>
        </DataGrid>
      )}
    </>
  );
};

export {AttributeGroupsDataGrid};
