import {useRoute} from '../../shared/router';
import {useCallback} from 'react';
import {MonitoringSettings} from '../../model/Apps/monitoring-settings';

export const useFetchConnectedAppMonitoringSettings = (connectionCode: string): (() => Promise<MonitoringSettings>) => {
    const url = useRoute('akeneo_connectivity_connection_apps_rest_get_connected_app_monitoring_settings', {
        connectionCode: connectionCode,
    });

    return useCallback(async () => {
        const response = await fetch(url, {
            method: 'GET',
            headers: [['X-Requested-With', 'XMLHttpRequest']],
        });
        if (false === response.ok) {
            return Promise.reject(`${response.status} ${response.statusText}`);
        }

        return response.json();
    }, [url]);
};
