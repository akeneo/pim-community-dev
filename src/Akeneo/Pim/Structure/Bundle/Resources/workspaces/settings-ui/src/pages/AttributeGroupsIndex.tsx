import React, {FC, useEffect} from 'react';
import {useTranslate} from "@akeneo-pim-community/legacy-bridge";
import {PageContent, PageHeader} from '@akeneo-pim-community/shared';
import {
    AttributeGroupsBreadcrumb,
    AttributeGroupsCreateButton,
    AttributeGroupsList,
    AttributeGroupsUserButtons
} from '../components';
import {useAttributeGroupsListState} from "../hooks";


const breadcrumb = <AttributeGroupsBreadcrumb />;
const userButtons = <AttributeGroupsUserButtons />;
const buttons = [<AttributeGroupsCreateButton />];

const AttributeGroupsIndex: FC = () => {
    const {groups, load} = useAttributeGroupsListState();
    const translate = useTranslate();

    useEffect(() => {
        load();
    }, []);

    return (
        <>
            <PageHeader breadcrumb={breadcrumb} userButtons={userButtons} buttons={buttons}>
                {translate('pim_enrich.entity.attribute_group.result_count', {count: groups.length.toString()}, groups.length)}
            </PageHeader>
            <PageContent>
                <AttributeGroupsList groups={groups} />
            </PageContent>
        </>
    );
};

export {AttributeGroupsIndex};
