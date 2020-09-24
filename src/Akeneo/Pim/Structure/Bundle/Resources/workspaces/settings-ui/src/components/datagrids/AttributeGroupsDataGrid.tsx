import React, {FC} from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {useAttributeGroupPermissions, useAttributeGroupsDataGridState} from '../../hooks';
import {AttributeGroupRow} from './AttributeGroupRow';
import {AttributeGroup} from "../../models";
import {DataGrid} from "../shared";

type Props = {
    groups: AttributeGroup[];
};

const AttributeGroupsDataGrid: FC<Props> = ({groups}) => {
    const {refreshOrder, compare} = useAttributeGroupsDataGridState();
    const {sortGranted, editGranted} = useAttributeGroupPermissions();
    const translate = useTranslate();

    return (
        <DataGrid
            isDraggable={sortGranted}
            dataSource={groups}
            handleAfterMove={refreshOrder}
            compareData={compare}
        >
            <DataGrid.HeaderRow>
                <DataGrid.Column>
                    {translate('pim_enrich.entity.attribute_group.grid.columns.name')}
                </DataGrid.Column>
            </DataGrid.HeaderRow>
            <DataGrid.Body>
                {groups.map((group, index) => (
                    <AttributeGroupRow key={group.code} group={group} isEditable={editGranted} index={index} />
                ))}
            </DataGrid.Body>
        </DataGrid>
   );
};

export {AttributeGroupsDataGrid};