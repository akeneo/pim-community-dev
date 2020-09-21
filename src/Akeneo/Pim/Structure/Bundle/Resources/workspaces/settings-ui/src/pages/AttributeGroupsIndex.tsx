import React, {FC} from 'react';
import {PageContent, PageHeader} from '@akeneo-pim-community/shared';
import {
    AttributeGroupsList,
    AttributeGroupsBreadcrumb,
    AttributeGroupsUserButtons,
    AttributeGroupsCreateButton
} from '../components';

const translate = require('oro/translator');

const breadcrumb = <AttributeGroupsBreadcrumb />;
const userButtons = <AttributeGroupsUserButtons />;
const buttons = [<AttributeGroupsCreateButton />];

const AttributeGroupsIndex: FC = () => {
    return (
        <>
            <PageHeader breadcrumb={breadcrumb} userButtons={userButtons} buttons={buttons}>
                {translate('pim_enrich.entity.attribute_group.plural_label')}
            </PageHeader>
            <PageContent>
                <AttributeGroupsList />
            </PageContent>
        </>
    );
};

export {AttributeGroupsIndex};
