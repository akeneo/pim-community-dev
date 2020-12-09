import React, {FC, useCallback, useState} from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {
  useAttributeGroupPermissions,
  useAttributeGroupsIndexState,
  useFilteredAttributeGroups,
  useGetAttributeGroupLabel,
} from '../../../hooks';
import {AttributeGroup} from '../../../models';
import {DataGrid, NoResults} from '../../shared';
import {debounce} from 'lodash';
import {SearchBar} from '@akeneo-pim-community/shared/src';
import {StatusBadge} from './StatusBadge';

const FeatureFlags = require('pim/feature-flags');

type Props = {
  groups: AttributeGroup[];
};

const AttributeGroupsDataGrid: FC<Props> = ({groups}) => {
  const {refreshOrder, compare, saveOrder, redirect} = useAttributeGroupsIndexState();
  const {sortGranted, editGranted} = useAttributeGroupPermissions();
  const getLabel = useGetAttributeGroupLabel();
  const {filteredGroups, search} = useFilteredAttributeGroups(groups);
  const translate = useTranslate();
  const [searchString, setSearchString] = useState('');

  const debouncedSearch = useCallback(debounce(search, 300), [groups]);

  const onSearch = (searchValue: string) => {
    setSearchString(searchValue);
    debouncedSearch(searchValue);
  };

  return (
    <>
      <SearchBar
        count={filteredGroups.length}
        searchValue={searchString === undefined ? '' : searchString}
        onSearchChange={onSearch}
      />
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
