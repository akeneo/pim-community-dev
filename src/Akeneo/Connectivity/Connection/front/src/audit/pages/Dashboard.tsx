import React, {memo, useEffect} from 'react';
import {Breadcrumb, Helper, HelperLink, HelperTitle, PageContent, PageHeader} from '../../common';
import {PimView} from '../../infrastructure/pim-view/PimView';
import {AuditEventType} from '../../model/audit-event-type.enum';
import {BreadcrumbRouterLink} from '../../shared/router';
import {Translate} from '../../shared/translate';
import {connectionsFetched} from '../actions/dashboard-actions';
import {DashboardContent} from '../components/DashboardContent';
import {useDashboardDispatch} from '../dashboard-context';
import {useConnections} from '../hooks/api/use-connections';
import {useFetchConnectionsAuditData} from '../hooks/api/use-fetch-connections-audit-data';

export const Dashboard = memo(() => {
    const {connections} = useConnections();

    const dispatch = useDashboardDispatch();
    useEffect(() => {
        if (undefined === connections) {
            return;
        }

        dispatch(connectionsFetched(connections));
    }, [connections, dispatch]);

    useFetchConnectionsAuditData(AuditEventType.PRODUCT_CREATED);
    useFetchConnectionsAuditData(AuditEventType.PRODUCT_UPDATED);
    useFetchConnectionsAuditData(AuditEventType.PRODUCT_READ);

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
            viewName='pim-connectivity-connection-user-navigation'
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
                    <HelperLink href='https://help.akeneo.com/pim/articles/connection-dashboard.html' target='_blank'>
                        <Translate id='akeneo_connectivity.connection.dashboard.helper.link' />
                    </HelperLink>
                </Helper>

                <DashboardContent />
            </PageContent>
        </>
    );
});
