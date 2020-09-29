import React, {FC, useCallback, useEffect, useState} from 'react';
import {useTranslate, useUserContext} from '@akeneo-pim-community/legacy-bridge';
import {useAttributeGroupPermissions, useAttributeGroupsDataGridState, useGetAttributeGroupLabel} from '../../../hooks';
import {AttributeGroup} from '../../../models';
import {DataGrid} from '../../shared';
import {ColumnLabel} from "./ColumnLabel";
import {debounce} from 'lodash';

const FeatureFlags = require("pim/feature-flags");

type Props = {
  groups: AttributeGroup[];
};

const AttributeGroupsDataGrid: FC<Props> = ({groups}) => {
  const {refreshOrder, compare, saveOrder, redirect} = useAttributeGroupsDataGridState();
  const {sortGranted, editGranted} = useAttributeGroupPermissions();
  const getLabel = useGetAttributeGroupLabel();
  const translate = useTranslate();
  const userContext = useUserContext();
  const [searchString, setSearchString] = useState('');
  const [filteredGroups, setFilteredGroups] = useState<AttributeGroup[]>([]);

  useEffect(() => {
    setFilteredGroups(groups);
  }, [groups]);

  const debouncedSearch = useCallback(
    debounce((searchValue: string) => {
      setFilteredGroups(
        Object.values(groups).filter((group: AttributeGroup) => group.labels[userContext.get('uiLocale')].toLowerCase().includes(searchValue.toLowerCase()))
      );
    }, 300), [groups]
  );

  const onSearch = (searchValue: string) => {
    setSearchString(searchValue);
    debouncedSearch(searchValue);
  };

  return (
    <DataGrid
      isReorderAllowed={sortGranted}
      isReorderActive={filteredGroups.length === groups.length}
      dataSource={filteredGroups}
      handleAfterMove={refreshOrder}
      compareData={compare}
      searchValue={searchString}
      onSearch={onSearch}
    >
      <DataGrid.HeaderRow>
        <DataGrid.Column>{translate('pim_enrich.entity.attribute_group.grid.columns.name')}</DataGrid.Column>
        {
          FeatureFlags.isEnabled('data_quality_insights') &&
          <DataGrid.Column>{translate('akeneo_data_quality_insights.attribute_group.dqi_status')}</DataGrid.Column>
        }
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
        {filteredGroups.map((group) => (
          <DataGrid.Row
            key={group.code}
            data={group}
          >
            <DataGrid.Column>
              <ColumnLabel>{getLabel(group)}</ColumnLabel>
            </DataGrid.Column>
            {
              FeatureFlags.isEnabled('data_quality_insights') &&
              <DataGrid.Column>
              <span className={`AknDataQualityInsightsQualityBadge AknDataQualityInsightsQualityBadge--${group.isDqiActivated ? 'good' : 'to-improve'}`}>
                {translate(`akeneo_data_quality_insights.attribute_group.${group.isDqiActivated ? 'activated' : 'disabled'}`)}
              </span>
              </DataGrid.Column>
            }
          </DataGrid.Row>
        ))}
      </DataGrid.Body>
    </DataGrid>
  );
};

export {AttributeGroupsDataGrid};
