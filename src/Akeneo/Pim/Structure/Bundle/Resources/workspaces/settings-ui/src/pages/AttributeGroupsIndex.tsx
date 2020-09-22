import React, {FC} from 'react';
import {useTranslate} from "@akeneo-pim-community/legacy-bridge";
import {PageContent, PageHeader} from '@akeneo-pim-community/shared';
import {
    AttributeGroupsList,
    AttributeGroupsBreadcrumb,
    AttributeGroupsUserButtons,
    AttributeGroupsCreateButton
} from '../components';
import {useAllAttributeGroups} from "../hooks";


const breadcrumb = <AttributeGroupsBreadcrumb />;
const userButtons = <AttributeGroupsUserButtons />;
const buttons = [<AttributeGroupsCreateButton />];

const AttributeGroupsIndex: FC = () => {
    const groups = useAllAttributeGroups();
    const translate = useTranslate();

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
