import React, {FC} from 'react';
import {ConnectedApp} from '../../../model/Apps/connected-app';
import {ConnectedAppAuthorizations} from './ConnectedAppAuthorizations';
import {ConnectedAppMonitoringSettings} from './ConnectedAppMonitoringSettings';
import {MonitoringSettings} from '../../../model/Apps/monitoring-settings';
import {Authentication} from './Settings/Authentication';
import {ConnectedAppCredentials} from './Settings/ConnectedAppCredentials';

type Props = {
    connectedApp: ConnectedApp;
    monitoringSettings: MonitoringSettings | null;
    handleSetMonitoringSettings: (monitoringSettings: MonitoringSettings) => void;
};

export const ConnectedAppSettings: FC<Props> = ({connectedApp, monitoringSettings, handleSetMonitoringSettings}) => {
    return (
        <>
            <ConnectedAppMonitoringSettings
                monitoringSettings={monitoringSettings}
                handleSetMonitoringSettings={handleSetMonitoringSettings}
            />
            <ConnectedAppAuthorizations connectedApp={connectedApp} />
            <Authentication connectedApp={connectedApp} />
            {connectedApp.is_custom_app && <ConnectedAppCredentials connectedApp={connectedApp} />}
        </>
    );
};
