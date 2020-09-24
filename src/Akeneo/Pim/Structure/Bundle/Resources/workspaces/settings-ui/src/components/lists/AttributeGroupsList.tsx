import React, {FC} from 'react';
import {PimView, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {Table, TableBody, TableContainer, TableHead, TableHeadCell, TableRow} from '../shared';
import {useSortAttributeGroupsIsGranted} from '../../hooks';
import {AttributeGroupRow} from './AttributeGroupRow';
import {AttributeGroup} from "../../models";
import {withDragState} from "../shared/hoc";

type Props = {
    groups: AttributeGroup[];
};

const AttributeGroupsList: FC<Props> = ({groups}) => {
    const sortIsGranted = useSortAttributeGroupsIsGranted();
    const translate = useTranslate();

    return (
        <TableContainer>
            <Table>
                <TableHead>
                    <TableRow>
                        {sortIsGranted && (<TableHeadCell />)}
                        <TableHeadCell>{translate('pim_enrich.entity.attribute_group.grid.columns.name')}</TableHeadCell>
                    </TableRow>
                </TableHead>
                <TableBody>
                    {groups.map((group, index) => (
                        <AttributeGroupRow key={group.code} group={group} isSortable={sortIsGranted} index={index} />
                    ))}
                </TableBody>
            </Table>
        </TableContainer>
   );
};

const DraggableList = withDragState(AttributeGroupsList);

export {DraggableList as AttributeGroupsList};