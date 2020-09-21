import React, {FC} from 'react';
import {PimView, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {Table, TableBody, TableContainer, TableRow, TableHead, TableHeadCell} from '../shared';
import {useAllAttributeGroups, useSortAttributeGroupsIsGranted} from '../../hooks';
import {AttributeGroupRow} from './AttributeGroupRow';


const AttributeGroupsList: FC = () => {
    const groups = useAllAttributeGroups();
    const sortIsGranted = useSortAttributeGroupsIsGranted();
    const translate = useTranslate();

    return (
        <>
            <TableContainer>
                <Table>
                    <TableHead>
                        <TableRow>
                            {sortIsGranted && (
                                <TableHeadCell />
                            )}
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