import React, {FC} from 'react';
import {ConnectedApp} from '../../../model/Apps/connected-app';
import {ConnectedAppAuthorizations} from './ConnectedAppAuthorizations';
import {ConnectedAppMonitoringSettings} from './ConnectedAppMonitoringSettings';
import {MonitoringSettings} from '../../../model/Apps/monitoring-settings';
import {Authentication} from './Settings/Authentication';
import {ConnectedAppCredentials} from './Settings/ConnectedAppCredentials';
import {useSecurity} from '../../../shared/security';

type Props = {
    connectedApp: ConnectedApp;
    monitoringSettings: MonitoringSettings | null;
    handleSetMonitoringSettings: (monitoringSettings: MonitoringSettings) => void;
};

export const ConnectedAppSettings: FC<Props> = ({connectedApp, monitoringSettings, handleSetMonitoringSettings}) => {
    const security = useSecurity();

    const showConnectedAppCredentials =
        connectedApp.is_custom_app && security.isGranted('akeneo_connectivity_connection_manage_test_apps');

    return (
        <>
            <ConnectedAppMonitoringSettings
                monitoringSettings={monitoringSettings}
                handleSetMonitoringSettings={handleSetMonitoringSettings}
            />
            <ConnectedAppAuthorizations connectedApp={connectedApp} />
            <Authentication connectedApp={connectedApp} />
            {showConnectedAppCredentials && <ConnectedAppCredentials connectedApp={connectedApp} />}
        </>
    );
};
