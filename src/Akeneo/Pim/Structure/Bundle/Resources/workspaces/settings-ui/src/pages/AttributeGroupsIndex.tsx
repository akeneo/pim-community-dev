import React, {FC, useEffect} from 'react';
import {useTranslate} from "@akeneo-pim-community/legacy-bridge";
import {PageContent, PageHeader} from '@akeneo-pim-community/shared';
import {
    AttributeGroupsBreadcrumb,
    AttributeGroupsCreateButton,
    AttributeGroupsDataGrid,
    AttributeGroupsUserButtons
} from '../components';
import {useAttributeGroupsDataGridState} from "../hooks";


const breadcrumb = <AttributeGroupsBreadcrumb />;
const userButtons = <AttributeGroupsUserButtons />;
const buttons = [<AttributeGroupsCreateButton />];

const AttributeGroupsIndex: FC = () => {
    const {groups, load} = useAttributeGroupsDataGridState();
    const translate = useTranslate();

    useEffect(() => {
        (async() => load())();
    }, []);

    return (
        <>
            <PageHeader breadcrumb={breadcrumb} userButtons={userButtons} buttons={buttons}>
                {translate('pim_enrich.entity.attribute_group.result_count', {count: groups.length.toString()}, groups.length)}
            </PageHeader>
            <PageContent>
                <AttributeGroupsDataGrid groups={groups} />
            </PageContent>
        </>
    );
};

export {AttributeGroupsIndex};
