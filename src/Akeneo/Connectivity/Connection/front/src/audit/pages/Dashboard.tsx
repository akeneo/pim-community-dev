import React, {memo, useEffect} from 'react';
import {Helper, HelperLink, HelperTitle, PageContent, PageHeader} from '../../common';
import {PimView} from '../../infrastructure/pim-view/PimView';
import {AuditEventType} from '../../model/audit-event-type.enum';
import {useRoute} from '../../shared/router';
import {Translate} from '../../shared/translate';
import {connectionsFetched} from '../actions/dashboard-actions';
import {DashboardContent} from '../components/DashboardContent';
import {useDashboardDispatch} from '../dashboard-context';
import {useConnections} from '../hooks/api/use-connections';
import {useFetchConnectionsAuditData} from '../hooks/api/use-fetch-connections-audit-data';
import {Breadcrumb} from 'akeneo-design-system';

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

    const dashboardHref = `#${useRoute('pim_dashboard_index')}`;

    const breadcrumb = (
        <Breadcrumb>
            <Breadcrumb.Step href={dashboardHref}>
                <Translate id='pim_menu.tab.activity' />
            </Breadcrumb.Step>
            <Breadcrumb.Step>
                <Translate id='pim_menu.item.connection_audit' />
            </Breadcrumb.Step>
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
