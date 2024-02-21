import React, {FC, useEffect, useState} from 'react';
import {FlowType} from '../../../../model/flow-type.enum';
import {ConnectionErrors} from '../../../../error-management/components/ConnectionErrors';
import {ConnectedApp} from '../../../../model/Apps/connected-app';
import {NotDataSourceConnectedApp} from './NotDataSourceConnectedApp';
import {NotAuditableConnectedApp} from './NotAuditableConnectedApp';
import {useFetchConnectedAppMonitoringSettings} from '../../../hooks/use-fetch-connected-app-monitoring-settings';
import {MonitoringSettings} from '../../../../model/Apps/monitoring-settings';
import {ErrorMonitoringError} from './ErrorMonitoringError';

type Props = {
    connectedApp: ConnectedApp;
};

export const ConnectedAppErrorMonitoring: FC<Props> = ({connectedApp}) => {
    const fetchMonitoringSettings = useFetchConnectedAppMonitoringSettings(connectedApp.connection_code);
    const [monitoringSettings, setMonitoringSettings] = useState<MonitoringSettings | null | false>(null);

    useEffect(() => {
        fetchMonitoringSettings()
            .then(setMonitoringSettings)
            .catch(() => setMonitoringSettings(false));
    }, []);

    if (null === monitoringSettings) {
        return null;
    }

    if (false === monitoringSettings) {
        return <ErrorMonitoringError />;
    }

    if (FlowType.DATA_SOURCE !== monitoringSettings.flowType) {
        return <NotDataSourceConnectedApp />;
    }

    if (!monitoringSettings.auditable) {
        return <NotAuditableConnectedApp />;
    }

    return (
        <ConnectionErrors
            connectionCode={connectedApp.connection_code}
            description={
                'akeneo_connectivity.connection.connect.connected_apps.edit.error_monitoring.helper.description'
            }
        />
    );
};
