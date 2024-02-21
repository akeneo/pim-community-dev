import {useRoute} from '../../shared/router';
import {useCallback} from 'react';
import {MonitoringSettings} from '../../model/Apps/monitoring-settings';

type CallbackType = (data: MonitoringSettings) => Promise<void>;

export const useSaveConnectedAppMonitoringSettings = (connectionCode: string): CallbackType => {
    const url = useRoute('akeneo_connectivity_connection_apps_rest_get_connected_app_monitoring_settings', {
        connectionCode: connectionCode,
    });

    return useCallback(
        async data => {
            const response = await fetch(url, {
                method: 'POST',
                headers: [['X-Requested-With', 'XMLHttpRequest']],
                body: JSON.stringify(data),
            });
            if (false === response.ok) {
                return Promise.reject(`${response.status} ${response.statusText}`);
            }

            return Promise.resolve();
        },
        [url]
    );
};
