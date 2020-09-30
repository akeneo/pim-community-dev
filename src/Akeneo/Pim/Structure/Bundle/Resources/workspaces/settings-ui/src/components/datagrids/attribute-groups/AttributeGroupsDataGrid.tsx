import React, {FC} from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {useAttributeGroupPermissions, useAttributeGroupsDataGridState, useGetAttributeGroupLabel} from '../../../hooks';
import {AttributeGroup} from '../../../models';
import {DataGrid} from '../../shared';
import {ColumnLabel} from "./ColumnLabel";

const FeatureFlags = require("pim/feature-flags");

type Props = {
  groups: AttributeGroup[];
};

const AttributeGroupsDataGrid: FC<Props> = ({groups}) => {
  const {refreshOrder, compare, saveOrder, redirect} = useAttributeGroupsDataGridState();
  const {sortGranted, editGranted} = useAttributeGroupPermissions();
  const getLabel = useGetAttributeGroupLabel();
  const translate = useTranslate();

  return (
    <DataGrid
      isDraggable={sortGranted}
      dataSource={groups}
      handleAfterMove={refreshOrder}
      compareData={compare}
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
        {groups.map((group) => (
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
