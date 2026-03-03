import React, {FC, memo} from 'react';
import {useHistory, useParams} from 'react-router';
import {Loading, PageContent, PageHeader} from '../../common';
import {FlowType} from '../../model/flow-type.enum';
import {Translate} from '../../shared/translate';
import {ConnectionErrors} from '../components/ConnectionErrors';
import {NotAuditableConnection} from '../components/NotAuditableConnection';
import {NotDataSourceConnection} from '../components/NotDataSourceConnection';
import {useConnection} from '../hooks/api/use-connection';
import {Breadcrumb} from 'akeneo-design-system';
import {UserButtons} from '../../shared/user';
import {useRouter} from '../../shared/router/use-router';

const ConnectionMonitoring: FC = memo(() => {
    const history = useHistory();
    const {connectionCode} = useParams<{connectionCode: string}>();
    const generateUrl = useRouter();

    const {loading, connection} = useConnection(connectionCode);
    if (loading || !connection) {
        return <Loading />;
    }

    const breadcrumb = (
        <Breadcrumb>
            <Breadcrumb.Step href={`#${generateUrl('akeneo_connectivity_connection_audit_index')}`}>
                <Translate id='pim_menu.tab.connect' />
            </Breadcrumb.Step>
            <Breadcrumb.Step href={history.createHref({pathname: '/connect/connection-settings'})}>
                <Translate id='pim_menu.item.connect_connection_settings' />
            </Breadcrumb.Step>
            <Breadcrumb.Step>
                <Translate id='akeneo_connectivity.connection.error_management.connection_monitoring.title' />
            </Breadcrumb.Step>
        </Breadcrumb>
    );

    return (
        <>
            <PageHeader breadcrumb={breadcrumb} userButtons={<UserButtons />}>
                {connection.label}
            </PageHeader>

            <PageContent>
                {FlowType.DATA_SOURCE !== connection.flow_type ? (
                    <NotDataSourceConnection />
                ) : !connection.auditable ? (
                    <NotAuditableConnection />
                ) : (
                    <ConnectionErrors
                        connectionCode={connectionCode}
                        description={
                            'akeneo_connectivity.connection.error_management.connection_monitoring.helper.description'
                        }
                    />
                )}
            </PageContent>
        </>
    );
});

export {ConnectionMonitoring};
