import React, {FC} from 'react';
import {Breadcrumb, BreadcrumbItem} from '@akeneo-pim-community/shared';

const translate = require('oro/translator');

const AttributeGroupsBreadcrumb: FC = () => {
    return (
        <Breadcrumb>
            <BreadcrumbItem>{translate('pim_menu.tab.settings')}</BreadcrumbItem>
            <BreadcrumbItem>{translate('pim_enrich.entity.attribute_group.plural_label')}</BreadcrumbItem>
        </Breadcrumb>
    );
}

export {AttributeGroupsBreadcrumb};
