import React, {useState, useEffect} from 'react';
import {PageHeader, PageContent, PimView, useRoute, useTranslate} from '@akeneo-pim-community/shared';
import {Breadcrumb} from 'akeneo-design-system';
import {useGroupTypes} from '../hooks';


const DatagridState = require('pim/datagrid/state');

const GroupTypesIndex = () => {
    const translate = useTranslate();
    const settingsHomeRoute = useRoute('pim_settings_index');

    const {groupTypes, search} = useGroupTypes();

    return (
        <>
            <PageHeader>
                <PageHeader.Breadcrumb>
                    <Breadcrumb>
                        <Breadcrumb.Step href={`#${settingsHomeRoute}`}>{translate('pim_menu.tab.settings')}</Breadcrumb.Step>
                        <Breadcrumb.Step>{translate('pim_menu.item.association_type')}</Breadcrumb.Step>
                    </Breadcrumb>
                </PageHeader.Breadcrumb>
                <PageHeader.UserActions>
                    <PimView
                        viewName="pim-menu-user-navigation"
                        className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
                    />
                </PageHeader.UserActions>
                <PageHeader.Actions>
                    <PimView viewName="pim-group-type-index-create-button" />
                </PageHeader.Actions>
                {null !== groupTypes && (
                    <PageHeader.Title>
                        {translate(
                            'pim_enrich.entity.group_type.page_title.index',
                            {count: groupTypes.total},
                            groupTypes.total
                        )}
                    </PageHeader.Title>
                )}
            </PageHeader>
            <PageContent>

            </PageContent>
        </>
    );
};

export {GroupTypesIndex};
