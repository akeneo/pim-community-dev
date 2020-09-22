import React, {FC} from 'react';
import {PimView, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {Table, TableBody, TableContainer, TableHead, TableHeadCell, TableRow} from '../shared';
import {useSortAttributeGroupsIsGranted} from '../../hooks';
import {AttributeGroupRow} from './AttributeGroupRow';
import {AttributeGroup} from "../../models";

type Props = {
    groups: AttributeGroup[];
};
const AttributeGroupsList: FC<Props> = ({groups}) => {
    const sortIsGranted = useSortAttributeGroupsIsGranted();
    const translate = useTranslate();

    return (
        <>
            <TableContainer>
                <Table>
                    <TableHead>
                        <TableRow>
                            {sortIsGranted && (<TableHeadCell />)}
                            <TableHeadCell>{translate('pim_enrich.entity.attribute_group.grid.columns.name')}</TableHeadCell>
                        </TableRow>
                    </TableHead>
                    <TableBody>
                        {groups.map((group) => (
                            <AttributeGroupRow key={group.code} group={group} isSortable={sortIsGranted} />
                        ))}
                    </TableBody>
                </Table>
            </TableContainer>

            {/* @todo remove code after finishing dev */}
            <hr />

            <PimView
                className=''
                viewName='pim-attribute-group-index-list'
            />

        </>
   );
};

export {AttributeGroupsList};