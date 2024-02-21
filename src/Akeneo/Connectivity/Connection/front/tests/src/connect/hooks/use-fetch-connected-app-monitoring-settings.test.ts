import {renderHook} from '@testing-library/react-hooks';
import {mockFetchResponses} from '../../../test-utils';

import {useFetchConnectedAppMonitoringSettings} from '@src/connect/hooks/use-fetch-connected-app-monitoring-settings';

test('it fetches the connected app monitor settings', async () => {
    const expectedMonitorSettings = {
        flow_type: 'data_source',
        auditable: true,
    };

    mockFetchResponses({
        'akeneo_connectivity_connection_apps_rest_get_connected_app_monitoring_settings?connectionCode=connectionCodeA':
            {
                json: expectedMonitorSettings,
            },
    });
    const {result} = renderHook(() => useFetchConnectedAppMonitoringSettings('connectionCodeA'));
    const monitorSettings = await result.current();

    expect(monitorSettings).toStrictEqual(expectedMonitorSettings);
});
