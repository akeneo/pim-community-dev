import React, {FC} from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {useAttributeGroupPermissions, useAttributeGroupsIndexState, useGetAttributeGroupLabel} from '../../../hooks';
import {AttributeGroup} from '../../../models';
import {DataGrid} from '../../shared';
import {CellLabel} from "./CellLabel";

type Props = {
  groups: AttributeGroup[];
};

const AttributeGroupsDataGrid: FC<Props> = ({groups}) => {
  const {refreshOrder, compare, saveOrder, redirect} = useAttributeGroupsIndexState();
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
        <DataGrid.Cell>{translate('pim_enrich.entity.attribute_group.grid.columns.name')}</DataGrid.Cell>
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
            <DataGrid.Cell>
              <CellLabel>{getLabel(group)}</CellLabel>
            </DataGrid.Cell>
          </DataGrid.Row>
        ))}
      </DataGrid.Body>
    </DataGrid>
  );
};

export {AttributeGroupsDataGrid};
