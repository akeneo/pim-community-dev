import React, {memo, useEffect} from 'react';
import {Helper, HelperLink, HelperTitle, PageContent, PageHeader} from '../../common';
import {AuditEventType} from '../../model/audit-event-type.enum';
import {useRoute} from '../../shared/router';
import {Translate} from '../../shared/translate';
import {connectionsFetched} from '../actions/dashboard-actions';
import {DashboardContent} from '../components/DashboardContent';
import {useDashboardDispatch} from '../dashboard-context';
import {useConnections} from '../hooks/api/use-connections';
import {useFetchConnectionsAuditData} from '../hooks/api/use-fetch-connections-audit-data';
import {Breadcrumb} from 'akeneo-design-system';
import {UserButtons} from '../../shared/user';
import {useFeatureFlags} from '../../shared/feature-flags';

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

    const dashboardHref = `#${useRoute('akeneo_connectivity_connection_audit_index')}`;

    const featureFlags = useFeatureFlags();

    const breadcrumb = (
        <Breadcrumb>
            <Breadcrumb.Step href={dashboardHref}>
                <Translate id='pim_menu.tab.connect' />
            </Breadcrumb.Step>
            <Breadcrumb.Step>
                <Translate id='pim_menu.item.data_flows' />
            </Breadcrumb.Step>
        </Breadcrumb>
    );

    return (
        <>
            <PageHeader breadcrumb={breadcrumb} userButtons={<UserButtons />}>
                <Translate id='pim_menu.item.data_flows' />
            </PageHeader>

            <PageContent>
                {!featureFlags.isEnabled('free_trial') && (
                    <Helper>
                        <HelperTitle>
                            <Translate id='akeneo_connectivity.connection.dashboard.helper.title' />
                        </HelperTitle>
                        <p>
                            <Translate id='akeneo_connectivity.connection.dashboard.helper.description' />
                        </p>
                        <HelperLink
                            href='https://help.akeneo.com/pim/articles/connection-dashboard.html'
                            target='_blank'
                        >
                            <Translate id='akeneo_connectivity.connection.dashboard.helper.link' />
                        </HelperLink>
                    </Helper>
                )}
                <DashboardContent />
            </PageContent>
        </>
    );
});
