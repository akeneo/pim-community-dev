import React, {FC, memo} from 'react';
import {useHistory, useParams} from 'react-router';
import {Loading, PageContent, PageHeader} from '../../common';
import {PimView} from '../../infrastructure/pim-view/PimView';
import {FlowType} from '../../model/flow-type.enum';
import {useRoute} from '../../shared/router';
import {Translate} from '../../shared/translate';
import {ConnectionErrors} from '../components/ConnectionErrors';
import {NotAuditableConnection} from '../components/NotAuditableConnection';
import {NotDataSourceConnection} from '../components/NotDataSourceConnection';
import {useConnection} from '../hooks/api/use-connection';
import {Breadcrumb} from 'akeneo-design-system';

const ConnectionMonitoring: FC = memo(() => {
    const history = useHistory();
    const {connectionCode} = useParams<{connectionCode: string}>();
    const systemHref = `#${useRoute('oro_config_configuration_system')}`;

    const {loading, connection} = useConnection(connectionCode);
    if (loading || !connection) {
        return <Loading />;
    }

    const breadcrumb = (
        <Breadcrumb>
            <Breadcrumb.Step href={systemHref}>
                <Translate id='pim_menu.tab.system' />
            </Breadcrumb.Step>
            <Breadcrumb.Step href={history.createHref({pathname: '/connections'})}>
                <Translate id='pim_menu.item.connection_settings' />
            </Breadcrumb.Step>
            <Breadcrumb.Step>
                <Translate id='akeneo_connectivity.connection.error_management.connection_monitoring.title' />
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
                {connection.label}
            </PageHeader>

            <PageContent>
                {FlowType.DATA_SOURCE !== connection.flow_type ? (
                    <NotDataSourceConnection />
                ) : !connection.auditable ? (
                    <NotAuditableConnection />
                ) : (
                    <ConnectionErrors connectionCode={connectionCode} />
                )}
            </PageContent>
        </>
    );
});

export {ConnectionMonitoring};
