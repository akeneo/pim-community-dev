import React, {useContext} from 'react';
import {PimView} from '../../infrastructure/pim-view/PimView';
import {Breadcrumb, Helper, HelperLink, HelperTitle, PageContent, PageHeader} from '../../common';
import {BreadcrumbRouterLink} from '../../shared/router';
import {Translate, TranslateContext} from '../../shared/translate';
import {Charts} from '../components/Charts';

export const Dashboard = () => {
    const translate = useContext(TranslateContext);

    const breadcrumb = (
        <Breadcrumb>
            <BreadcrumbRouterLink route={'pim_dashboard_index'} isLast={false}>
                <Translate id='pim_menu.tab.activity' />
            </BreadcrumbRouterLink>
        </Breadcrumb>
    );

    const userButtons = (
        <PimView
            className='AknTitleContainer-userMenuContainer AknTitleContainer-userMenu'
            viewName='pim-apps-user-navigation'
        />
    );

    return (
        <>
            <PageHeader breadcrumb={breadcrumb} userButtons={userButtons}>
                <Translate id='pim_menu.item.connection_audit' />
            </PageHeader>

            <PageContent>
                <Helper>
                    <HelperTitle>
                        <Translate id='akeneo_connectivity.connection.dashboard.helper.title' />
                    </HelperTitle>
                    <p>
                        <Translate id='akeneo_connectivity.connection.dashboard.helper.description' />
                    </p>
                    <HelperLink href={translate('akeneo_connectivity.connection.dashboard.helper.link_url')} target='_blank'>
                        <Translate id='akeneo_connectivity.connection.dashboard.helper.link' />
                    </HelperLink>
                </Helper>

                <Charts />
            </PageContent>
        </>
    );
};
