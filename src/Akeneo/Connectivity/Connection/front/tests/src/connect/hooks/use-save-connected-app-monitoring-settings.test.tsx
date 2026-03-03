import {renderHook} from '@testing-library/react-hooks';
import fetchMock from 'jest-fetch-mock';
import {mockFetchResponses} from '../../../test-utils';
import {useSaveConnectedAppMonitoringSettings} from '@src/connect/hooks/use-save-connected-app-monitoring-settings';
import {FlowType} from '@src/model/flow-type.enum';

beforeEach(() => {
    fetchMock.resetMocks();
});

test('it saves the connected app monitoring settings', async done => {
    const {result} = renderHook(() => useSaveConnectedAppMonitoringSettings('connectionCodeA'));

    const data = {flowType: FlowType.OTHER, auditable: false};
    await result.current(data);

    expect(fetchMock).toBeCalledWith(
        'akeneo_connectivity_connection_apps_rest_get_connected_app_monitoring_settings?connectionCode=connectionCodeA',
        {
            method: 'POST',
            headers: [['X-Requested-With', 'XMLHttpRequest']],
            body: JSON.stringify(data),
        }
    );
    done();
});

test('it rejects when the connected app could not be saved', async done => {
    mockFetchResponses({
        'akeneo_connectivity_connection_apps_rest_get_connected_app_monitoring_settings?connectionCode=connectionCodeA':
            {
                status: 422,
                json: {},
            },
    });

    const {result} = renderHook(() => useSaveConnectedAppMonitoringSettings('connectionCodeA'));
    await expect(result.current({flowType: FlowType.OTHER, auditable: false})).rejects.toEqual(
        '422 Unprocessable Entity'
    );
    done();
});
